<?php
namespace BlackwoodSeven\Tests\File;

use BlackwoodSeven\File\CsvFileObject;
use BlackwoodSeven\File\AtomicTempFileObjects;

class AtomicTempFileObjectsUnitTest extends BlackwoodSevenFileUnitTestBase
{
    public function testSplitCsvFile()
    {
        $csvFile = $this->fixturesPath . '/csvfile4-input';
        $csvFileObject = new CsvFileObject($csvFile);

        $dirname = $this->tempdirnam();

        $destFiles = new AtomicTempFileObjects();
        $destFiles->splitCsvFile($csvFileObject, function ($row) use ($dirname) {
            return $dirname . '/csvfile4-result-' . $row['no'];
        });
        $destFiles->createDirectoryOnPersist();
        $destFiles->persistOnClose();
        unset($destFiles);

        $this->assertTrue(file_exists($dirname . '/csvfile4-result-1'), 'File was not split correctly.');
        $this->assertTrue(file_exists($dirname . '/csvfile4-result-2'), 'File was not split correctly.');
        $this->assertTrue(file_exists($dirname . '/csvfile4-result-3'), 'File was not split correctly.');

        $expected = file_get_contents($this->fixturesPath . '/csvfile4-result-1');
        $result = file_get_contents($dirname . '/csvfile4-result-1');
        $this->assertEquals($expected, $result, 'File was not split correctly.');

        $expected = file_get_contents($this->fixturesPath . '/csvfile4-result-2');
        $result = file_get_contents($dirname . '/csvfile4-result-2');
        $this->assertEquals($expected, $result, 'File was not split correctly.');

        $expected = file_get_contents($this->fixturesPath . '/csvfile4-result-3');
        $result = file_get_contents($dirname . '/csvfile4-result-3');
        $this->assertEquals($expected, $result, 'File was not split correctly.');

    }
}
