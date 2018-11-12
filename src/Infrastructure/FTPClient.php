<?php
declare(strict_types=1);

namespace App\Infrastructure;

class FTPClient {

    public function put($path, $fileName) {
        ftp_pasv($this->conn, true);

        ftp_chdir($this->conn, $path);

        ftp_put($this->conn, $fileName, $fileName, FTP_ASCII);

        return unlink($fileName);
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
        $this->conn = ftp_connect($ftp_ip);

        if (!$this->conn) {
            throw new \ErrorException('Connection failed');
        }
        ftp_login($this->conn, $username, $password);
    }

    public function __destruct() {
        ftp_close($this->conn);
    }

    private $conn;
}