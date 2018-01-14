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
    public function getUserTransactions() : array
    {
        return $this->transactions;
    }

    /**
     * @return string
     */
    public function getUserType() : string
    {
        return $this->userType;
    }

    public function setUserTransactions(array $transactions)
    {
        $this->transactions = $transactions;
    }
}

?>