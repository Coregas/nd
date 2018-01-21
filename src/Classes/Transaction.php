<?php
namespace Paysera\Classes;
use DateTime;

class Transaction
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var int
     */
    private $userId;
    /**
     * @var string
     */
    private $userType;
    /**
     * @var string
     */
    private $transactionType;
    /**
     * @var float
     */
    private $amount;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var float
     */
    private $commissionFee;

    public function __construct(
        $id,
        $date,
        $userId,
        $userType,
        $transactionType,
        $amount,
        $currency
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->transactionType = $transactionType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }
    /**
     * @return DateTime
     */
    public function getDate() : DateTime
    {
        return $this->date;
    }
    /**
     * @return int
     */
    public function getYear() : int
    {
        return $this->date->format('o');
    }

    /**
     * @return int
     */
    public function getWeek(): int
    {
        return $this->date->format('W');
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getUserType() : string
    {
        return $this->userType;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->transactionType;
    }

    /**
     * @return float
     */
    public function getAmount() : float
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getCurrency() : string
    {
        return $this->currency;
    }

    /**
     * @param $commisionFee
     */
    public function setCommissionFee($commisionFee)
    {
        $this->commissionFee = $commisionFee;
    }

    /**
     * @return mixed
     */
    public function getCommissionFee()
    {
        return $this->commissionFee;
    }
}