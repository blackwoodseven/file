<?php

namespace BlackwoodSeven\File;

class AtomicTempFileObject extends \SplFileObject
{
    const DISCARD = 1;
    const PERSIST = 2;
    const PERSIST_UNCHANGED = 4;

    protected $destinationRealPath;
    protected $mTime = false;
    protected $mode = false;
    protected $persist = 0;
    protected $onPersistCallback;
    protected $onDiscardCallback;

    /**
     * Constructor.
     */
    public function __construct(string $filename)
    {
        $tempDir = dirname(realpath($filename));
        $tempPrefix = basename($filename) . '.AtomicTempFileObject.';
        $this->destinationRealPath = $filename;
        parent::__construct(tempnam($tempDir, $tempPrefix), "w+");
    }

    /**
     * Get the destination real path.
     *
     * @return string
     *   The real path of the destination.
     */
    public function getDestinationRealPath(): string
    {
        return $this->destinationRealPath;
    }

    /**
     * Set modified time stamp of persistent file.
     *
     * @param int $mTime
     *   File modification time in unix timestamp.
     */
    public function setModifiedTime($mTime): AtomicTempFileObject
    {
        $this->mTime = $mTime;
        return $this;
    }

    /**
     * Auto create directory for destination file upon persist.
     */
    public function createDirectoryOnPersist($mode = 0777)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * Move temp file into the destination upon object desctruction.
     */
    public function persistOnClose($persist = self::PERSIST): AtomicTempFileObject
    {
        $this->persist = $persist;
        return $this;
    }

    public function onPersist(callable $callback)
    {
        $this->onPersistCallback = $callback;
    }

    public function onDiscard(callable $callback)
    {
        $this->onDiscardCallback = $callback;
    }

    private function doPersist()
    {
        if ($this->mTime !== false) {
            if (!@touch($this->getRealPath(), $this->mTime)) {
                $last_error = error_get_last();
                throw new \RuntimeException(sprintf("Could not set modified time on %s to %d - message: %s",
                    $this->getRealPath(), $this->mTime, $last_error['message']
                ));
            }
        }
        if ($this->mode !== false) {
            $path = dirname($this->destinationRealPath);
            if (!file_exists($path)) {
                if (!@mkdir($path, $this->mode, true)) {
                    $last_error = error_get_last();
                    throw new \RuntimeException(sprintf("Could create directories for %s - message: %s",
                        $this->getRealPath(), $last_error['message']
                    ));
                }
            }
        }
        if (!@rename($this->getRealPath(), $this->destinationRealPath)) {
            $last_error = error_get_last();
            throw new \RuntimeException(sprintf("Could not move %s to %s - message: %s",
                $this->getRealPath(), $this->destinationRealPath, $last_error['message']
            ));
        }
        if ($this->onPersistCallback) {
            call_user_func($this->onPersistCallback, $this);
        }
    }

    private function doDiscard()
    {
        if (!@unlink($this->getRealPath())) {
            $last_error = error_get_last();
            throw new \RuntimeException(sprintf("Could not remove %s - message: %s",
                $this->getRealPath(), $last_error['message']
            ));
        }
        if ($this->onDiscardCallback) {
            call_user_func($this->onDiscardCallback, $this);
        }
    }

    /**
     * Move temp file into the destination if applicable.
     */
    public function __destruct()
    {
        $this->fflush();
        if ($this->persist & (self::PERSIST | self::PERSIST_UNCHANGED)) {
            if ($this->persist & self::PERSIST_UNCHANGED || !$this->compare($this->destinationRealPath)) {
                $this->doPersist();
            }
            else {
                $this->doDiscard();
            }
        }
        elseif ($this->persist & self::DISCARD) {
            $this->doDiscard();
        }
        else {
            trigger_error("Temp file left on device: " . $this->getRealPath(), E_USER_WARNING);
        }
    }

    /**
     * Easy access iterator apply for processing an entire file.
     *
     * @param  Iterator $input    [description]
     * @param  callable $callback [description]
     */
    public function process(\Iterator $input, callable $callback)
    {
        $input->rewind();
        iterator_apply($input, function (\Iterator $iterator) use ($callback) {
            call_user_func($callback, $iterator->current(), $iterator->key(), $iterator, $this);
            return true;
        }, [$input]);
        return $this;
    }

    /**
     * Atomic file_put_contents().
     *
     * @see file_put_contents()
     */
    static public function file_put_contents($filename, $data)
    {
        $tempFile = new static($filename);
        $tempFile->fwrite($data);
        $tempFile->persistOnClose();
        unset($tempFile);
    }

    /**
     * File comparison
     *
     * @param string $filename
     *   The file to check against.
     *
     * @return bool
     *   True if the contents of this file matches the contents of $filename.
     */
    public function compare($filename): bool
    {
        if (!file_exists($filename)) {
            return false;
        }

        // This is a temp file opened for writing and truncated to begin with,
        // so we assume that the current position is the size of the new file.
        $pos = $this->ftell();

        $file = new \SplFileObject($filename, 'r');
        if ($pos <> $file->getSize()) {
            return false;
        }

        // Rewind this temp file and compare it with the specified file.
        $identical = true;
        $this->fseek(0);
        while(!$file->eof()) {
            if($file->fread(8192) != $this->fread(8192)) {
                $identical = false;
                break;
            }
        }

        // Reset file pointer to end of file.
        $this->fseek($pos);
        return $identical;
    }
}
