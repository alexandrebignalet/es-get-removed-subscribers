<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\CSVWriter;
use App\Domain\RemovedSubscriber;
use PHPUnit\Framework\TestCase;

final class CSVWriterTest extends TestCase {

    public function testShouldContainsColumnsGiven() {

        $expectedHeaders = ['Date', 'Email', 'Raison'];
        $csvWriter = new CSVWriter();

        $csvContent = $csvWriter->generate($expectedHeaders, [])->getContent();

        $stringToHeaderArray = preg_split('/[\s,]+/', $csvContent);
        $actualHeaders = array_splice($stringToHeaderArray, 0, count($expectedHeaders));
        $this->assertEquals(count($expectedHeaders), count($actualHeaders));
        foreach ($expectedHeaders as $header) {
            $this->assertContains($header, $actualHeaders);
        }
    }

    public function testShouldAppendAsManyLinesAsRecordsGiven() {
        $headers = ['Date', 'Email', 'Raison'];
        $records = array_map(function() { return [1, 2, 3]; }, range(0, 10));
        $csvWriter = new CSVWriter();

        $csvContent = $csvWriter->generate($headers, $records)->getContent();
        $stringToHeaderAndRecordsArray = preg_split('/[\n]/', trim($csvContent));
        $actualRecords = array_splice($stringToHeaderAndRecordsArray, 1, count($stringToHeaderAndRecordsArray));

        $this->assertEquals(count($records), count($actualRecords));
    }

    public function testShouldFormatRemovedSubscribersInCSVStringRecords() {
        $expectedHeaders = RemovedSubscriber::csvHeader();
        $removedSubscriberAlexObj = new RemovedSubscriber('1', 'alex@gmail.com', '2', new \DateTimeImmutable('yesterday'), ['OptOutLink']);
        $removedSubscribers = [
            $removedSubscriberAlexObj,
            new RemovedSubscriber('2', 'alexandre@gmail.com', '5', new \DateTimeImmutable('yesterday'), ['Ui'])
        ];

        $csvWriter = new CSVWriter();

        $csvContent = $csvWriter->generateRemovedSubscribersCSV($removedSubscribers)->getContent();

        $stringToHeaderAndRecordsArray = preg_split('/[\n]/', trim($csvContent));
        $actualRecords = array_splice($stringToHeaderAndRecordsArray, 0, count($stringToHeaderAndRecordsArray));

        $this->assertEquals($expectedHeaders, preg_split('/,/', $actualRecords[0]));

        $headerLine = 1;
        $this->assertEquals(count($removedSubscribers) + $headerLine, count($actualRecords));

        $removedSubscriberAlexArr = preg_split('/,/', $actualRecords[1]);
        $this->assertEquals($removedSubscriberAlexArr[0], $removedSubscriberAlexObj->getUnsubscribedOn()->format('d/m/Y'));
        $this->assertEquals($removedSubscriberAlexArr[1], $removedSubscriberAlexObj->getEmail());
        $this->assertEquals($removedSubscriberAlexArr[2], $removedSubscriberAlexObj->getRejectionReasons()[0]);
    }

    public function testShouldCreateANewFileAccordingOnPathAndNameGiven() {
        $removedSubscribers = [
            new RemovedSubscriber('1', 'alex@gmail.com', '2', new \DateTimeImmutable('yesterday'), ['OptOutLink']),
            new RemovedSubscriber('2', 'alexandre@gmail.com', '5', new \DateTimeImmutable('yesterday'), ['Ui'])
        ];

        $csvWriter = new CSVWriter();
        $fileName = '' . 'acheterlouerremoved-' . (new \DateTimeImmutable("now"))->format('dmY') . '.csv';
        $csvWriter->createFile($removedSubscribers, $fileName);

        $this->assertFileExists($fileName);

        unlink($fileName);
    }
}