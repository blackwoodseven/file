## Installation

To install the BlackwoodSeven file library in your project using Composer:

```composer require blackwoodseven/file```

## Usage: CsvFileObject

```php

use BlackwoodSeven\File\CsvFileObject;

$csvFile = new CsvFileObject('my-data.csv');
$data = iterator_to_array($csvFile);

$fields = $csvFile->getFields();
// or
$fields = array_keys(reset($data));

```

## Usage: AtomicTempFileObject

```php

use BlackwoodSeven\File\AtomicTempFileObject;

$newFile = new AtomicTempFileObject('my-output.csv');
$newFile->putcsv($fields);

foreach ($data as $row) {
    $newFile->putcsv($row);
}

$newfile->persistOnClose();
unset($newfile);

```


## Usage: AtomicTempFileObjects

```php

use BlackwoodSeven\File\CsvFileObject;
use BlackwoodSeven\File\AtomicTempFileObjects;

$inputFile = new CsvFileObject('my-input.csv');
$outputFiles = new AtomicTempFileObjects();

// Split a csv file into multiple files.
$outputFiles->splitCsvFile($inputFile, function (&$row) {
    // $row is by reference and can be modified.
    $row = mapTheRowAsIWantItToBe($row);

    // Return filename to use for the specific row.
    return 'my-output.' . $row['date'] . '.csv';
});

$outputFiles->persistOnClose();
unset($outputFiles);

```

```php

use BlackwoodSeven\File\CsvFileObject;
use BlackwoodSeven\File\AtomicTempFileObjects;

$inputFile = new \SplFileObject('my-input.csv');
$outputFiles = new AtomicTempFileObjects();

// Split a file into two files containing odd and even lines.
$outputFiles->process($inputFile, function ($line, $lineNum, $input, $output) {
    $no = ($lineNum % 2);
    $fileName = 'my-output.' . ($no ? 'even' : 'odd') . '.txt';
    $file = $output->isFileOpen($fileName) ? $output->getFile($fileName) : $output->openFile($fileName);
    $file->fwrite($line);
});

$outputFiles->persistOnClose();
unset($outputFiles);

```
