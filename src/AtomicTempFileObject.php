<?php

namespace BlackwoodSeven\File;

class AtomicTempFileObject extends \SplFileObject
{
    protected $destinationRealPath;
    protected $mTime;
    protected $persist = false;

    /**
     * Constructor.
     */
    public function __construct($filename)
    {
        $tempDir = dirname(realpath($filename));
        $tempPrefix = 'AtomicTempFileObject';
        $this->destinationRealPath = $filename;
        parent::__construct(tempnam($tempDir, $tempPrefix), "w+");
    }

    /**
     * Set modified time stamp of persistent file.
     *
     * @param int $mTime
     *   File modification time in unix timestamp.
     */
    public function setModifiedTime($mTime)
    {
        $this->mTime = $mTime;
    }

    /**
     * Move temp file into the destination upon object desctruction.
     */
    public function persistOnClose($persist = true)
    {
        $this->persist = $persist;
    }

    /**
     * Move temp file into the destination if applicable.
     */
    public function __destruct()
    {
        if ($this->persist && !$this->compare($this->destinationRealPath)) {
            if (isset($this->mTime)) {
                touch($this->getRealPath(), $this->mTime);
            }
            rename($this->getRealPath(), $this->destinationRealPath);
        }
        else {
            unlink($this->getRealPath());
        }
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
    public function compare($filename)
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
