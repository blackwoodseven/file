<?php
namespace BlackwoodSeven\Tests\File;

use BlackwoodSeven\File\CsvFileObject;

class CsvFileObjectUnitTest extends \PHPUnit_Framework_TestCase
{
    protected $fixturesPath = __DIR__ . '/fixtures';

    public function dataProvider()
    {
        return [
            [$this->fixturesPath . '/csvfile1-input', $this->fixturesPath . '/csvfile1-result'],
        ];
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testCsvParsing($csvFile, $expectedResultFile)
    {
        $parsedData = iterator_to_array(new CsvFileObject($csvFile));
        $this->assertEquals(json_decode(file_get_contents($expectedResultFile), true), $parsedData, 'Unexpected parsing of csv file');
    }
}
