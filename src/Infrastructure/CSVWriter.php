<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\RemovedSubscriber;
use League\Csv\AbstractCsv;
use League\Csv\Writer;

class CSVWriter {

    public function __construct() {}

    public function generate(array $headers, array $records, string $path = null): AbstractCsv {
        $csv = $path ? Writer::createFromPath($path, 'w') : Writer::createFromString('');

        $csv->insertOne($headers);

        $csv->insertAll($records);

        return $csv;
    }

    /**
     * @param array $removedSubscribers
     * @param string $path
     * @return AbstractCsv
     */
    public function generateRemovedSubscribersCSV(array $removedSubscribers, string $path = null): AbstractCsv {
        $headers = RemovedSubscriber::csvHeader();

        /** @var RemovedSubscriber $removeSubscriber
         * @return Callable
         */
        $toCSVRecords = function($removeSubscriber) { return $removeSubscriber->toCSVRecord(); };

        $records = array_map($toCSVRecords, $removedSubscribers);

        return $this->generate($headers, $records, $path);
    }

    public function createFile(array $removedSubscribers, string $fileName) {

        $this->generateRemovedSubscribersCSV($removedSubscribers, $fileName);

        return $fileName;
    }
}