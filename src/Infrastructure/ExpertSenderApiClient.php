<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\RemovedSubscriber;
use GuzzleHttp\Client;

class ExpertSenderApiClient {

    /**
     * @param $rejectionReason
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     * @return RemovedSubscriber[]
     * @throws \Exception
     */
    public function getRemovedSubscribersByRejectionReason($rejectionReason, $startDate, $endDate = null): array {
        $endDate = $endDate ? $endDate->format('Y-m-d') : $startDate;

        $queryParams = [
            'apiKey' => $this->apiKey,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'removeTypes' => $rejectionReason
        ];

        $removedSubscribersXmlArray = $this->get('RemovedSubscriber', $queryParams);

        return array_reduce($removedSubscribersXmlArray, function($acc, $removedSubscriberXml) use($rejectionReason) {
            $removeSubscriber = RemovedSubscriber::of($removedSubscriberXml, $rejectionReason);
            $acc[$removeSubscriber->getId()] = $removeSubscriber;
            return $acc;
        }, []);
    }

    public function get($resourceName, $queryParams) {
        $response = $this->httpClient->get($this->apiUrl . '/' . $resourceName . 's', [
            'query' => $queryParams
        ]);

        $removedSubscribersXmlArray = $response->xml()->Data->RemovedSubscribers->RemovedSubscriber;

        return $this->xmlToArray($removedSubscribersXmlArray);
    }

    private function xmlToArray(\SimpleXMLElement $xmlObject): array {
        $removedSubscribersXml = [];

        foreach ($xmlObject as $removedSubscriberXml) {
            $removedSubscribersXml[] = $removedSubscriberXml;
        }

        return $removedSubscribersXml;
    }

    public function __construct(Client $httpClient, string $apiKey) {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->apiUrl = getenv('ES_API_URL');
    }

    private $apiUrl;
    private $apiKey;
    private $httpClient;
}