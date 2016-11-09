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
            [$this->fixturesPath . '/csvfile2-input', $this->fixturesPath . '/csvfile2-result'],
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

    /**
     * @dataProvider dataProvider()
     */
    public function testCsvFields($csvFile, $expectedResultFile)
    {
        $csvFileObject = new CsvFileObject($csvFile);
        $content = json_decode(file_get_contents($expectedResultFile), true);
        $expectedFields = array_keys(reset($content));
        $this->assertEquals($expectedFields, $csvFileObject->getFields(), 'Unexpected fields found');
    }

    /**
     * @dataProvider dataProvider()
     */
    public function testCsvCount($csvFile, $expectedResultFile)
    {
        $expectedCount = count(json_decode(file_get_contents($expectedResultFile), true));

        $csvFileObject = new CsvFileObject($csvFile);
        $this->assertEquals($expectedCount, count($csvFileObject), 'Lines were not counted correctly');

        $csvFileObject = new CsvFileObject($csvFile);
        foreach ($csvFileObject as $row) {}
        $this->assertEquals($expectedCount, count($csvFileObject), 'Lines were not counted correctly');

        $csvFileObject = new CsvFileObject($csvFile);
        $csvFileObject->seek(3);
        $this->assertEquals($expectedCount, count($csvFileObject), 'Lines were not counted correctly');
    }

    public function testCsvSeek()
    {
        $csvFile = $this->fixturesPath . '/csvfile2-input';
        $csvFileObject = new CsvFileObject($csvFile);
        $csvFileObject->seek(2);
        $this->assertEquals(2, $csvFileObject->key(), 'Seek did not succeed');
        $this->assertEquals('tomorrow3', $csvFileObject->current()['date'], 'Seek did not succeed');

        $csvFileObject->seek(1);
        $this->assertEquals(1, $csvFileObject->key(), 'Seek did not succeed');
        $this->assertEquals('tomorrow2', $csvFileObject->current()['date'], 'Seek did not succeed');

        $csvFileObject->seek(4);
        $this->assertEquals(4, $csvFileObject->key(), 'Seek did not succeed');
        $this->assertEquals('tomorrow5', $csvFileObject->current()['date'], 'Seek did not succeed');

        $csvFileObject->seek(0);
        $this->assertEquals(0, $csvFileObject->key(), 'Seek did not succeed');
        $this->assertEquals('tomorrow1', $csvFileObject->current()['date'], 'Seek did not succeed');

        $csvFileObject->seek(100);
        $this->assertEquals(5, $csvFileObject->key(), 'Seek did not succeed');
        $this->assertEquals(null, $csvFileObject->current()['date'], 'Seek did not succeed');
    }
}
