<?php
declare(strict_types=1);

namespace App\Tests\Service;


use App\Infrastructure\ExpertSenderApiClient;
use App\Domain\RemovedSubscriber;
use App\Service\RemovedSubscriberService;
use PHPUnit\Framework\TestCase;

class RemovedSubscribersServiceTest extends TestCase {

    public function testShouldGatherRemovedSubscribersUnifyingReasons() {
        $removedSubscribersOptOutLink = [
            new RemovedSubscriber('1', 'alex@gmail.com', '2', new \DateTimeImmutable('yesterday'), ['OptOutLink'])
        ];
        $removedSubscribersUi = [
            new RemovedSubscriber('1', 'alex@gmail.com', '2', new \DateTimeImmutable('yesterday'), ['Ui']),
        ];
        $removedSubscribersComplaint = [
            new RemovedSubscriber('1', 'alex@gmail.com', '2', new \DateTimeImmutable('yesterday'), ['Complaint'])
        ];

        $apiClientStub = $this->createMock(ExpertSenderApiClient::class);
        $apiClientStub->method('getRemovedSubscribersByRejectionReason')
            ->will($this->onConsecutiveCalls(
                $removedSubscribersComplaint,
                $removedSubscribersUi,
                $removedSubscribersOptOutLink,
                [], [], []
                ));

        /** @var ExpertSenderApiClient $apiClientStub */
        $removedSubscriberService = new RemovedSubscriberService($apiClientStub);

        $removedSubscribers = $removedSubscriberService->getRemovedSubscribers();

        $this->assertEquals(count($removedSubscribers), 1);
        $expectedRemovedSub = new RemovedSubscriber('1', 'alex@gmail.com', '2', new \DateTimeImmutable('yesterday'), ['Complaint', 'Ui', 'OptOutLink']);
        $actualRemovedSubscriber = reset($removedSubscribers);
        $this->assertTrue($actualRemovedSubscriber == $expectedRemovedSub);
    }
}