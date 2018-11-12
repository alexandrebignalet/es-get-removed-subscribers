<?php
/**
 * Created by PhpStorm.
 * User: alexandrebignalet
 * Date: 09/11/2018
 * Time: 20:30
 */

namespace App\Command;


class GenerateRemovedSubscribersCSVCommand {
    private $customerName;
    private $path;
    private $rejectionReasons;

    /**
     * GenerateRemovedSubscribersCSVCommand constructor.
     * @param $rejectionReasons
     * @param $customerName
     * @param string $path
     */
    public function __construct($customerName, $path, $rejectionReasons = null) {
        $this->rejectionReasons = $rejectionReasons;
        $this->path = $path ?: '';
        $this->customerName = $customerName;
    }

    /**
     * @return mixed
     */
    public function rejectionReasons()
    {
        return $this->rejectionReasons;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function customerName()
    {
        return $this->customerName;
    }
}