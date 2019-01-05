<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\RemovedSubscriber;

class FTPClient {

    public function put($fileName) {
        ftp_pasv($this->conn, true);

        ftp_put($this->conn, $fileName, $fileName, FTP_ASCII);

        return unlink($fileName);
    }

    public function getRemovedSubscribersFromFile($path, $fileName) {
        ftp_pasv($this->conn, true);

        ftp_chdir($this->conn, $path);

        $data = [];
        ob_start();
        if(ftp_get($this->conn, 'php://output', $fileName, FTP_ASCII)) {
            $fileData = ob_get_contents();
            $data = $this->csvStringToRemoveSuscribers($fileData);
        }
        ob_end_clean();
        return $data;
    }

    public function delete($remoteFilePath) {
        return ftp_delete($this->conn, $remoteFilePath);
    }

    /**
     * FTPClient constructor.
     * @param string $ftp_ip
     * @param string $username
     * @param string $password
     * @throws \ErrorException
     */
    public function __construct(string $ftp_ip, string $username, string $password) {
        $this->conn = @ftp_connect($ftp_ip);

        if (!$this->conn) {
            throw new \ErrorException('Connection failed');
        }
        ftp_login($this->conn, $username, $password);
    }

    public function __destruct() {
        ftp_close($this->conn);
    }

    public $conn;

    /**
     * @param $fileData
     * @return array
     */
    public function csvStringToRemoveSuscribers($fileData): array
    {
        $csvRows = preg_split('/\n/', $fileData);

        array_shift($csvRows);
        array_pop($csvRows);

        $existingRemoveSubscribers = array_map(function ($row) {
            $args = preg_split('/,/', $row);
            return new RemovedSubscriber(null, $args[1], null, \DateTimeImmutable::createFromFormat('d/m/Y', $args[0]), preg_split('/,/', $args[2]));
        }, $csvRows);

        return $existingRemoveSubscribers;
    }
}