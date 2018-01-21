<?php
namespace Paysera\Classes;

class User {
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $userType;
    /**
     * @var array
     */
    private $transactions;

    function __construct(
        $id,
        $userType
    ) {
        $this->id = $id;
        $this->userType = $userType;
    }

    /**
     * @return int
     */
    public function getUserId():int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getTransactions() : array
    {
        return $this->transactions;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getTransactionsByType(string $type) : array
    {
       $sortedTransactions = [];

       foreach ($this->getTransactions() as $transaction) {
           if ($transaction->getType() == $type) {
               $sortedTransactions[] = $transaction;
           }
       }
       return $sortedTransactions;
    }
    /**
     * @return string
     */
    public function getUserType() : string
    {
        return $this->userType;
    }

    /**
     * @param array $transactions
     */
    public function setTransactions(array $transactions)
    {
        $this->transactions = $transactions;
    }
}
