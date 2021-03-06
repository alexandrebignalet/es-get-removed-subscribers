<?php

namespace App\Infrastructure\CommandLine;

use App\Command\CreateRemovedSubscribersCSVCommandHandler;
use App\Command\GenerateRemovedSubscribersCSVCommand;
use App\Infrastructure\CSVWriter;
use App\Infrastructure\ExpertSenderApiClient;
use App\Infrastructure\FTPClient;
use App\Service\RemovedSubscriberService;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAndSendRemovedSubscribersCSV extends Command
{
    protected function configure()
    {
        $this->setName("removed-subscribers:csv:send")
            ->setDescription("Send removed subscribers csv to the ftp")
            ->setHelp("This command allows you to create & send removed subscribers resources as csv on ftp...")
            ->setDefinition(
                new InputDefinition([
                    new InputOption("api-key", "k", InputOption::VALUE_REQUIRED, "The customer ES api key."),
                    new InputOption("ftp-ip", "i", InputOption::VALUE_REQUIRED, "TThe FTP server IP to put the generated file."),
                    new InputOption("ftp-username", "u", InputOption::VALUE_REQUIRED, "An username granted into the FTP server."),
                    new InputOption("ftp-password", "p", InputOption::VALUE_REQUIRED, "The password of the corresponding username."),
                    new InputOption("customer-name", "c", InputOption::VALUE_REQUIRED, "The name of the customer responsible."),
                    new InputOption("path", "a", InputOption::VALUE_OPTIONAL, "The path storage on the FTP server."),
                    new InputOption("rejection-reasons", "r", InputOption::VALUE_OPTIONAL, "The rejection reasons to match."),
                    new InputOption("start-date", "s", InputOption::VALUE_OPTIONAL, "From when start fetching."),
                    new InputOption("end-date", "e", InputOption::VALUE_OPTIONAL, "To when end fetching.")
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $apiKey = $input->getOption("api-key");
            $ftpIp = $input->getOption("ftp-ip");
            $ftpUsername = $input->getOption("ftp-username");
            $ftpPassword = $input->getOption("ftp-password");

            $handler = $this->createHandler($apiKey, $ftpIp, $ftpUsername, $ftpPassword);

            $command = $this->deserializeInput($input);

            $generationDate = $command->startDate() ?: new \DateTimeImmutable( "now");
            $output->writeln("Generation of the ".$generationDate->format("Y-m-d H:m:s").""."");

            $handler->handle($command);


            $output->writeln("Done. \n ******************* \n");

            return 0;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln("Existing due to error...");
            return 1;
        }
    }

    private function deserializeInput(InputInterface $input): GenerateRemovedSubscribersCSVCommand
    {
        return new GenerateRemovedSubscribersCSVCommand(
            $input->getOption("customer-name"),
            $input->getOption("path"),
            $input->getOption("rejection-reasons"),
            $input->getOption("start-date"),
            $input->getOption("end-date")
        );
    }

    /**
     * @param $apiKey
     * @return CreateRemovedSubscribersCSVCommandHandler
     * @throws \ErrorException
     */
    private function createHandler($apiKey, $ftpIp, $ftpUsername, $ftpPassword)
    {
        $httpClient = new Client();
        $esApiClient = new ExpertSenderApiClient($httpClient, $apiKey);
        $ftpClient = new FTPClient($ftpIp, $ftpUsername, $ftpPassword);
        $csvWriter = new CSVWriter();
        $removedSubscribersService = new RemovedSubscriberService($esApiClient);
        return new CreateRemovedSubscribersCSVCommandHandler($removedSubscribersService, $ftpClient, $csvWriter);
    }
}