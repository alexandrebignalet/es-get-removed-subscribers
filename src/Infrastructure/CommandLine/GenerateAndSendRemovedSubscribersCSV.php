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
        $this->setName('removed-subscribers:csv:send')
            ->setDescription('Send removed subscribers csv to the ftp')
            ->setHelp('This command allows you to create & send removed subscribers resources as csv on ftp...')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('api-key', 'k', InputOption::VALUE_REQUIRED, 'The customer ES api key.'),
                    new InputOption('customer-name', 'cn', InputOption::VALUE_REQUIRED, 'The name of the customer responsible.'),
                    new InputOption('path', 'p', InputOption::VALUE_OPTIONAL, 'The path storage on the FTP server.'),
                    new InputOption('rejection-reasons', 'rr', InputOption::VALUE_OPTIONAL, 'The rejection reasons to match.'),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $apiKey = $input->getOption('api-key');
            $handler = $this->createHandler($apiKey);

            $command = $this->deserializeInput($input);
            $handler->handle($command);

        } catch (\ErrorException $e) {
            $output->writeln($e->getMessage());
            $output->writeln('Existing due to error...');
            exit;
        }
    }

    private function deserializeInput(InputInterface $input): GenerateRemovedSubscribersCSVCommand
    {
        return new GenerateRemovedSubscribersCSVCommand(
            $input->getOption('customer-name'),
            $input->getOption('path'),
            $input->getOption('rejection-reasons')
        );
    }

    /**
     * @param $apiKey
     * @return CreateRemovedSubscribersCSVCommandHandler
     * @throws \ErrorException
     */
    private function createHandler($apiKey)
    {
        $esApiUrl = getenv('ES_API_URL');
        $ftp_ip = 'ftp.cluster021.hosting.ovh.net';
        $username = 'expertsegb-alex';
        $password = 'UhJ746YHr4';

        $httpClient = new Client();
        $esApiClient = new ExpertSenderApiClient($httpClient, $apiKey, $esApiUrl);
        $ftpClient = new FTPClient($ftp_ip, $username, $password);
        $csvWriter = new CSVWriter();
        $removedSubscribersService = new RemovedSubscriberService($esApiClient);
        return new CreateRemovedSubscribersCSVCommandHandler($removedSubscribersService, $ftpClient, $csvWriter);
    }
}