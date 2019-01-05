<?php
declare(strict_types=1);

namespace App\Service;

use App\Domain\RemovedSubscriber;
use App\Infrastructure\ExpertSenderApiClient;

class RemovedSubscriberService {
    private const removeTypes = ['OptOutLink', 'Ui', 'BounceLimit', 'Complaint', 'UserUnknown', 'Api'];

    /**
     * @param array $rejectionReasons
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     * @return array
     * @throws \Exception
     */
    public function getRemovedSubscribers($rejectionReasons = null, $startDate = null, $endDate = null) {
        $rejectionReasons = $rejectionReasons ? $rejectionReasons : RemovedSubscriberService::removeTypes;
        $removedSubscribers = [];

        foreach ($rejectionReasons as $rejectionReason) {
            $byRejectionReason = $this->apiClient->getRemovedSubscribersByRejectionReason($rejectionReason, $startDate, $endDate);
            $removedSubscribers = $this->mergeRemovedSubscribers($removedSubscribers, $byRejectionReason);
        }

        return $removedSubscribers;
    }

    private function mergeRemovedSubscribers(array $existing, array $toMerge): array {
        /**
         * @param RemovedSubscriber[] $existing
         * @param RemovedSubscriber $removeSubscriber
         * @return RemovedSubscriber[]
         */
        $createNewElemOrMergeRejectionReasons = function($existing, $removeSubscriber) {
            if (array_key_exists($removeSubscriber->getId(), $existing)) {
                /** @var RemovedSubscriber $existingRemoved */
                $existingRemoved = $existing[$removeSubscriber->getId()];
                $existing[$removeSubscriber->getId()] = $existingRemoved->addRejectionReason($removeSubscriber->getFirstRejectionReasons());
            } else {
                $existing[$removeSubscriber->getId()] = $removeSubscriber;
            }

            return $existing;
        };

        return array_reduce($toMerge, $createNewElemOrMergeRejectionReasons, $existing);
    }

    /** @param ExpertSenderApiClient $apiClient */
    public function __construct($apiClient) {
        $this->apiClient = $apiClient;
    }

    private $apiClient;
}