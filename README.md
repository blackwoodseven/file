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

$outputFiles->splitCsvFile($inputFile, function ($row) {
    // Return filename to use for the specific row.
    return 'my-output.' . $row['date'] . '.csv';
});

$outputFiles->persistOnClose();
unset($outputFiles);

```
