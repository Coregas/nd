<?php
namespace Paysera\Classes;

class User {
    /**
     * @var int
     */
    private $id;
    /**
     * @var array
     */
    private $transactions;

    function __construct(
        $id,
        $transactions
    ) {
        $this->id = $id;
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
    public function getUserTransactions()
    {
        return $this->transactions;
    }

    public function setUserTransactions(array $transactions):array
    {
        $this->transactions = $transactions;
    }
}

?>