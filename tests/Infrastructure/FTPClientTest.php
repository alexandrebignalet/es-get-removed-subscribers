<?php
declare(strict_types=1);

namespace App\Tests\Infrastructure;

use App\Infrastructure\CSVWriter;
use App\Infrastructure\FTPClient;
use App\Domain\RemovedSubscriber;
use PHPUnit\Framework\TestCase;

class FTPClientTest extends TestCase {
    private $createdFileName;
    /** @var FTPClient $ftpClient */
    private $ftpClient;

    public function setUp() {
        $ftp_ip = getenv('FTP_DOMAIN');
        $username = getenv('FTP_USERNAME');
        $password = getenv('FTP_PASSWORD');
        $this->ftpClient = new FTPClient($ftp_ip, $username, $password);
    }
    public function tearDown() {
        $this->ftpClient->delete($this->createdFileName);
    }

    public function testShouldConnectAndLoginWithGivenInfos() {
        $removedSubscribers = [
            new RemovedSubscriber('1', 'alex@gmail.com', '2', new \DateTime('yesterday'), ['OptOutLink']),
            new RemovedSubscriber('2', 'alexandre@gmail.com', '5', new \DateTime('yesterday'), ['Ui'])
        ];
        $csvWriter = new CSVWriter();

        $this->createdFileName = $csvWriter->createFile($removedSubscribers, 'acheterlouer');

        $this->assertTrue($this->ftpClient->put('', $this->createdFileName));
    }
}