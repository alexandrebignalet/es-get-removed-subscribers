<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\ExpertSenderApiClient;
use App\Domain\RemovedSubscriber;
use GuzzleHttp\Client;
use GuzzleHttp\Message\FutureResponse;
use PHPUnit\Framework\TestCase;

final class ExpertSenderApiClientTest extends TestCase
{
    public function setUp() {
        $this->httpClientMock = $this->getMockBuilder(Client::class)->setMethods(['get'])->getMock();
    }

    public function testShouldReturnRemovedSubscribers(): void {
        $stubResponse = $this->createMock(FutureResponse::class);
        $stubResponse->method('xml')->willReturn(new \SimpleXMLElement($this->mockResponse));
        $this->httpClientMock->expects($this->exactly(1))->method('get')->willReturn($stubResponse);
        $apiClient = new ExpertSenderApiClient($this->httpClientMock, 'aMockApiKey');

        $removedSubscribers = $apiClient->getRemovedSubscribersByRejectionReason($this->rejectionReason, new \DateTimeImmutable());

        $this->assertInternalType('array', $removedSubscribers);

        // reset first value
        $aRemovedSubscriber = reset($removedSubscribers);
        $this->assertObjectHasAttribute('id', $aRemovedSubscriber);
        $this->assertObjectHasAttribute('email', $aRemovedSubscriber);
        $this->assertObjectHasAttribute('listId', $aRemovedSubscriber);
        $this->assertObjectHasAttribute('unsubscribedOn', $aRemovedSubscriber);
    }

    public function testShouldReturnAnArrayOfRemovedSubscribersWithTheReasonOfRejection() {
        $stubResponse = $this->createMock(FutureResponse::class);
        $stubResponse->method('xml')->willReturn(new \SimpleXMLElement($this->mockResponse));
        $this->httpClientMock->expects($this->exactly(1))->method('get')->willReturn($stubResponse);
        $apiClient = new ExpertSenderApiClient($this->httpClientMock, 'aMockApiKey');

        $removedSubscribers = $apiClient->getRemovedSubscribersByRejectionReason($this->rejectionReason, new \DateTimeImmutable());

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

        $resources = $apiClientMock->getRemovedSubscribersByRejectionReason($this->rejectionReason, new \DateTimeImmutable());
        $this->assertEquals($resources, []);
    }

    public function testShouldReturnAOnItemCollectionOfResourcesIfOneXMLObjectIsRetrieved()
    {
        $xmlstr = <<<XML
        <ApiResponse xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <Data>
                <RemovedSubscribers>
                    <RemovedSubscriber>
                        <Id>455247</Id>
                        <Email>chantal.lagrange@numericable.fr</Email>
                        <ListId>5</ListId>
                        <UnsubscribedOn>2018-12-28T06:37:12.717</UnsubscribedOn>
                    </RemovedSubscriber>
                </RemovedSubscribers>
            </Data>
        </ApiResponse>
XML;
        $stubResponse = $this->createMock(FutureResponse::class);
        $stubResponse->method('xml')->willReturn(new \SimpleXMLElement($xmlstr));
        $this->httpClientMock->expects($this->exactly(1))->method('get')->willReturn($stubResponse);
        $apiClient = new ExpertSenderApiClient($this->httpClientMock, 'aMockApiKey');
        $expectedRemovedSubscriber = new RemovedSubscriber('455247', 'chantal.lagrange@numericable.fr', 5, new \DateTimeImmutable("2018-12-28T06:37:12.717"), [$this->rejectionReason]);

        $resources = $apiClient->getRemovedSubscribersByRejectionReason($this->rejectionReason, new \DateTimeImmutable());

        $this->assertEquals($resources, [$expectedRemovedSubscriber->getId() => $expectedRemovedSubscriber]);
    }

    private $rejectionReason = 'OptOutLink';
    private $httpClientMock;
    private $mockResponse = <<<XML
        <ApiResponse xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <Data>
                <RemovedSubscribers>
                    <RemovedSubscriber>
                <Id>711764</Id>
                <Email>colette.bontemps@wanadoo.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T20:14:03.913</UnsubscribedOn>
            </RemovedSubscriber>
            <RemovedSubscriber>
                <Id>714262</Id>
                <Email>bperruch@wanadoo.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T21:38:04.4</UnsubscribedOn>
            </RemovedSubscriber>
            <RemovedSubscriber>
                <Id>718056</Id>
                <Email>kdomenget@orange.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T08:40:05.777</UnsubscribedOn>
            </RemovedSubscriber>
            <RemovedSubscriber>
                <Id>720489</Id>
                <Email>abjsl@orange.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T03:42:03.917</UnsubscribedOn>
            </RemovedSubscriber>
            <RemovedSubscriber>
                <Id>724702</Id>
                <Email>chaptois.jocelyne4@orange.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T14:40:06.387</UnsubscribedOn>
            </RemovedSubscriber>
            <RemovedSubscriber>
                <Id>724915</Id>
                <Email>estelle.caplet@orange.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T16:34:03.62</UnsubscribedOn>
            </RemovedSubscriber>
            <RemovedSubscriber>
                <Id>729816</Id>
                <Email>pdanglade@wanadoo.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T07:58:04.977</UnsubscribedOn>
            </RemovedSubscriber>
            <RemovedSubscriber>
                <Id>734300</Id>
                <Email>anneguy.lavallee@orange.fr</Email>
                <ListId>25</ListId>
                <UnsubscribedOn>2018-12-28T18:24:05.297</UnsubscribedOn>
            </RemovedSubscriber>
                </RemovedSubscribers>
            </Data>
        </ApiResponse>
XML;
}