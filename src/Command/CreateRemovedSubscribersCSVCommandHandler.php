<?php
/**
 * Created by PhpStorm.
 * User: alexandrebignalet
 * Date: 09/11/2018
 * Time: 20:32
 */

namespace App\Command;


use App\Infrastructure\CSVWriter;
use App\Infrastructure\FTPClient;
use App\Service\RemovedSubscriberService;

class CreateRemovedSubscribersCSVCommandHandler {

    public function handle(GenerateRemovedSubscribersCSVCommand $command) {
        $startDate = $command->startDate();
        $previousPossibleDateFileGeneration = $command->startDate()->modify('-1 day');

        $newFileName = $command->customerName() . 'removed-' . $startDate->format('dmY') . '.csv';
        $previousFileName = $command->customerName() . 'removed-' . $previousPossibleDateFileGeneration->format('dmY') . '.csv';

        $existingRemovedSubscribersFromFile =  $this->ftpClient->getRemovedSubscribersFromFile($command->path(), $previousFileName);
        $newRemovedSubscribersFromApi = $this->removedSubscribersService->getRemovedSubscribers(
            $command->rejectionReasons(),
            $command->startDate(),
            $command->endDate()
        );

        $removedSubscribers = array_merge($existingRemovedSubscribersFromFile, $newRemovedSubscribersFromApi);

        $createdCsvName = $this->csvWriter->createFile($removedSubscribers, $newFileName);

        $this->ftpClient->put($createdCsvName);
    }

    private $removedSubscribersService;
    private $ftpClient;
    private $csvWriter;

    /**
     * CreateRemovedSubscribersCSVCommandHandler constructor.
     * @param $removedSubscribersService
     * @param $ftpClient
     * @param $csvWriter
     */
    public function __construct(RemovedSubscriberService $removedSubscribersService, FTPClient $ftpClient, CSVWriter $csvWriter) {
        $this->removedSubscribersService = $removedSubscribersService;
        $this->ftpClient = $ftpClient;
        $this->csvWriter = $csvWriter;
    }


}