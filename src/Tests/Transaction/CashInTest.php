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
    private $transactionEur;
    /**
     * @var Transaction
     */
    private $transactionUsd;
    /**
     * @var Transaction
     */
    private $transactionJpy;

    /**
     * @var Transaction
     */
    private $transactionFalseCurrency;

    public function setUp()
    {
       $this->config = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

       $this->service = new CashIn($this->config);

       $this->transactionEur = new Transaction(
           1,
           '2018-01-01',
           1,
           'natural',
           'cash_in',
           1000,
           'EUR'
       );

        $this->transactionUsd = new Transaction(
            1,
            '2018-01-01',
            1,
            'natural',
            'cash_in',
            1,
            'USD'
        );

        $this->transactionJpy = new Transaction(
            1,
            '2018-01-01',
            1,
            'natural',
            'cash_in',
            30000000,
            'JPY'
        );

        $this->transactionFalseCurrency = new Transaction(
            1,
            '2018-01-01',
            1,
            'natural',
            'cash_in',
            30000000,
            'LOL'
        );

    }

    public function testCommissionFeeEur()
    {
       $this->assertInternalType('float', $this->service->commissionFee($this->transactionEur));
    }

    public function testCommissionFeeUsd()
    {
        $this->assertInternalType('float', $this->service->commissionFee($this->transactionUsd));
    }

    public function testCommissionFeeJpy()
    {
        $this->assertInternalType('float', $this->service->commissionFee($this->transactionJpy));
    }

    /**
     * @expectedException \Exception
     */
    public function testCommissionFeeFalseCurrency()
    {
       $this->service->commissionFee($this->transactionFalseCurrency);
    }

}