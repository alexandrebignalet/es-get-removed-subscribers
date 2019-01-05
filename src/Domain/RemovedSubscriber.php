<?php
declare(strict_types=1);

namespace App\Domain;

final class RemovedSubscriber {

    /**
     * @param $removedSubscriberXml
     * @param $rejectionReason
     * @return RemovedSubscriber
     * @throws \Exception
     */
    public static function of($removedSubscriberXml, $rejectionReason) {
        $nodeValue = function($nodeName) use ($removedSubscriberXml) {
            return ((array) $removedSubscriberXml->$nodeName)[0];
        };

        $id = $nodeValue('Id');
        $email = $nodeValue('Email');
        $listId = $nodeValue('ListId');
        $unsubscribedOn = new \DateTimeImmutable($nodeValue('UnsubscribedOn'));

        return new RemovedSubscriber($id, $email, $listId, $unsubscribedOn, [$rejectionReason]);
    }

    public static function csvHeader() {
        return ['Date', 'Email', 'Raison(s)'];
    }

    public function toCSVRecord() {
        return [$this->getUnsubscribedOn()->format('d/m/Y'), $this->getEmail(), implode(',', $this->getRejectionReasons())];
    }

    private $id;
    private $email;
    private $listId;
    /** @var \DateTimeImmutable */
    private $unsubscribedOn;
    /** @var string[] */
    private $rejectionReasons;

    public function __construct($id, $email, $listId, $unsubscribedOn, $rejectionReasons) {
        $this->id = $id;
        $this->email = $email;
        $this->listId = $listId;
        $this->unsubscribedOn = $unsubscribedOn;
        $this->rejectionReasons = $rejectionReasons;
    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @return mixed
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @return mixed
     */
    public function getUnsubscribedOn(): \DateTimeImmutable
    {
        return $this->unsubscribedOn;
    }

    public function getRejectionReasons() {
        return $this->rejectionReasons;
    }

    public function addRejectionReason($reason) {
        $rejectionReasons = $this->getRejectionReasons();
        $rejectionReasons[] = $reason;

        return new RemovedSubscriber(
            $this->getId(),
            $this->getEmail(),
            $this->getListId(),
            $this->getUnsubscribedOn(),
            $rejectionReasons
        );
    }

    public function getFirstRejectionReasons() {
        return $this->getRejectionReasons()[0];
    }
}