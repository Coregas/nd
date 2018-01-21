<?php
namespace Paysera\Tests\Transaction;

use AppConfig\Config;
use Paysera\Classes\Transaction;
use \PHPUnit\Framework\TestCase;
use Paysera\Services\Transaction\CashIn;

class CashInTest extends TestCase
{
    private $config;

    /**
     * @var \Paysera\Services\Transaction\CashIn;
     */
    private $service;
    /**
     * @var Transaction
     */
    private $transaction;

    public function setUp()
    {
       $this->config = new Config();

       $this->service = new CashIn($this->config);

       $this->transaction = new Transaction(
           1,
           '2018-01-01',
           1,
           'natural',
           'cash_in',
           1000,
           'EUR'
       );
    }

    public function testCommissionFee()
    {
        $this->service->commissionFee($this->transaction);
    }

}