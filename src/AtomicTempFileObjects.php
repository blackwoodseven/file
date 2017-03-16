<?php

namespace BlackwoodSeven\File;

/**
 * Create multiple csv files in same format based on custom condition.
 */
class AtomicTempFileObjects
{
    protected $files = [];

    /**
     * Constructor.
     *
     * @param array $files
     *   The files to use.
     */
    public function __construct($files = [])
    {
        $this->files = $files;
    }

    /**
     * Get opened files.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get an already opened file.
     *
     * @param string $fileName
     *   The name of the file to get.
     *
     * @return AtomicTempFileObject
     *   The open file.
     */
    public function getFile($fileName): AtomicTempFileObject
    {
        if (!$this->isFileOpen($fileName)) {
            throw new \Exception("File: $fileName not opened!");
        }
        return $this->files[$fileName];
    }

    /**
     * Check if file is opened.
     *
     * @param string $fileName
     *   The name of the file to check.
     *
     * @return boolean
     *   True if opened, false if not.
     */
    public function isFileOpen($fileName): bool
    {
        return isset($this->files[$fileName]);
    }

    /**
     * Open a new atomic temp file.
     *
     * @param string $fileName
     *   The name of the file to open.
     *
     * @return AtomicTempFileObject
     *   The file opened.
     */
    public function openFile($fileName): AtomicTempFileObject {
        if ($this->isFileOpen($fileName)) {
            throw new \Exception("File: $fileName already opened!");
        }
        $this->files[$fileName] = new AtomicTempFileObject($fileName);
        return $this->files[$fileName];
    }

    /**
     * Add an already opened AtomicTempFileObject file.
     *
     * @param AtomicTempFileObject $file
     */
    public function addFile($file): AtomicTempFileObject
    {
        if ($this->isFileOpen($file->getDestinationPathname())) {
            throw new \Exception("File: " . $file->getDestinationPathname() . " already opened!");
        }
        $this->files[$file->getDestinationPathname()] = $file;
        return $this;
    }

    /**
     * Split a csv file into multiple csv files.
     *
     * @param  CsvFileObject $input
     *   The input to split.
     * @param  callable $callback
     *   A callback returning the filename for the specific row.
     * @return
     */
    public function splitCsvFile(CsvFileObject $input, callable $callback) {
        foreach ($input as $row) {
            $fileName = call_user_func($callback, $row);
            if (!$this->isFileOpen($fileName)) {
                $file = $this->openFile($fileName);
                $file->fputcsv(array_keys($row));
            }
            else {
                $file = $this->getFile($fileName);
            }
            $file->fputcsv($row);
        }
        return $this;
    }

    /**
     * Dispatch method calls to all attached AtomicTempFileObject.
     */
    public function __call($method, $arguments)
    {
        foreach ($this->files as $file) {
            call_user_func_array([$file, $method], $arguments);
        }
        return $this;
    }
}
