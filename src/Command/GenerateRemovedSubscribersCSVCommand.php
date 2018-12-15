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
    private $startDate;
    private $endDate;

    /**
     * GenerateRemovedSubscribersCSVCommand constructor.
     * @param $customerName
     * @param string $path
     * @param $rejectionReasons
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     */
    public function __construct($customerName, $path, $rejectionReasons = null, $startDate = null, $endDate = null) {
        $this->rejectionReasons = $rejectionReasons;
        $this->path = $path ?: '';
        $this->customerName = $customerName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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

    public function startDate() {
        if($this->startDate)
            return \DateTime::createFromFormat('d/m/Y', $this->startDate);

        return $this->startDate;
    }

    public function endDate() {
        if($this->endDate)
            return \DateTime::createFromFormat('d/m/Y', $this->endDate);

        return $this->endDate;
    }
}