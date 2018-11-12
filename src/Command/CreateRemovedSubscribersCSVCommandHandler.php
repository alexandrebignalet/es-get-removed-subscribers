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
        $removedSubscribers = $this->removedSubscribersService->getRemovedSubscribers($command->rejectionReasons());

        $createdCsvName = $this->csvWriter->createFile($removedSubscribers, $command->customerName());

        $this->ftpClient->put($command->path(), $createdCsvName);
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