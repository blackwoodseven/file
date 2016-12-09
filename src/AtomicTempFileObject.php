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
        if ($this->persist) {
            if (isset($this->mTime)) {
                touch($this->getRealPath(), $this->mTime);
            }
            rename($this->getRealPath(), $this->destinationRealPath);
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
}
