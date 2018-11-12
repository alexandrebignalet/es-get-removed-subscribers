<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\RemovedSubscriber;
use GuzzleHttp\Client;

class ExpertSenderApiClient {

    /**
     * @param $rejectionReason
     * @param null $startDate
     * @param null $endDate
     * @return RemovedSubscriber[]
     */
    public function getRemovedSubscribersByRejectionReason($rejectionReason, $startDate = null, $endDate = null): array {
        $today = (new \DateTime('now'))->format('Y-m-d');

        $queryParams = [
            'apiKey' => $this->apiKey,
            'startDate' => $startDate ?: $today,
            'endDate' => $endDate ?: $today,
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

        $removedSubscribersXmlArray = $response->xml()->Data->RemovedSubscribers;

        $xmlResources = $this->xmlToArray($removedSubscribersXmlArray);
        return array_key_exists($resourceName, $xmlResources) ? $xmlResources[$resourceName] : [];
    }

    private function xmlToArray($xmlObject): array {
        return array_map(function($node) {
            return is_object($node) ? $this->xmlToArray($node) : $node;
        }, (array) $xmlObject);
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