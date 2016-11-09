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
    public function __construct($filename, $tempDir = null, $tempPrefix = null, $mTime = null)
    {
        $tempDir = $tempDir ?? sys_get_temp_dir();
        $tempPrefix = $tempPrefix ?? 'AtomicTempFileObject';
        $this->destinationRealPath = $filename;
        $this->mTime = $mTime;
        parent::__construct(tempnam($tempDir, $tempPrefix), "w");
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
