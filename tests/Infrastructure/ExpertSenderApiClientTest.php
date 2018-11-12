<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\ExpertSenderApiClient;
use App\Domain\RemovedSubscriber;
use DateTime;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

final class ExpertSenderApiClientTest extends TestCase
{
    private $apiKey = 'npqHSIjt2l9enlHf2ZLk';
    private $rejectionReason = 'OptOutLink';

    public function testShouldReturnRemovedSubscribers(): void {
        $client = new ExpertSenderApiClient(new Client(), $this->apiKey);
        $removedSubscribers = $client->getRemovedSubscribersByRejectionReason($this->rejectionReason);

        $this->assertInternalType('array', $removedSubscribers);

        // reset first value
        $aRemovedSubscriber = reset($removedSubscribers);
        $this->assertObjectHasAttribute('id', $aRemovedSubscriber);
        $this->assertObjectHasAttribute('email', $aRemovedSubscriber);
        $this->assertObjectHasAttribute('listId', $aRemovedSubscriber);
        $this->assertObjectHasAttribute('unsubscribedOn', $aRemovedSubscriber);
    }

    public function testShouldReturnRemovedSubscribersOfTodayByDefault() {
        $client = new ExpertSenderApiClient(new Client(), $this->apiKey);
        $removedSubscribers = $client->getRemovedSubscribersByRejectionReason($this->rejectionReason);

        $startOfToday = new DateTime("now");
        $startOfToday->setTime(0, 0, 0);
        $endOfToday = new DateTime("tomorrow");
        $endOfToday->setTime(0, 0, 0);

        /** @var RemovedSubscriber $removedSubscriber */
        foreach ($removedSubscribers as $removedSubscriber) {
            $this->assertGreaterThan($startOfToday, $removedSubscriber->getUnsubscribedOn());
            $this->assertLessThan($endOfToday, $removedSubscriber->getUnsubscribedOn());
        }
    }

    public function testShouldReturnAnArrayOfRemovedSubscribersWithTheReasonOfRejection() {
        $client = new ExpertSenderApiClient(new Client, $this->apiKey);
        $removedSubscribers = $client->getRemovedSubscribersByRejectionReason($this->rejectionReason);

        /** @var RemovedSubscriber $removedSubscriber */
        foreach ($removedSubscribers as $key => $removedSubscriber) {
            $this->assertEquals($key, $removedSubscriber->getId());
            $this->assertEquals($this->rejectionReason, $removedSubscriber->getRejectionReasons()[0]);
        }
    }

    public function testShouldReturnAnEmptyCollectionOfResourcesIfNothingIsRetrieved()
    {
        $httpClient = new Client();
        $apiClientMock = $this->getMockBuilder(ExpertSenderApiClient::class)
            ->setConstructorArgs([$httpClient, 'aMockedApiKey'])
            ->setMethods(['get'])
            ->getMock();

        $apiClientMock->expects($this->exactly(1))->method('get')->willReturn([]);

        $resources = $apiClientMock->getRemovedSubscribersByRejectionReason($this->rejectionReason);
        $this->assertEquals($resources, []);
    }
}