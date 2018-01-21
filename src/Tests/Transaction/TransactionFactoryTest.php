<?php
namespace Paysera\Tests\Transaction;

use Paysera\Classes\Transaction;
use DateTime;
use Paysera\Services\Transaction\TransactionFactory;
use PHPUnit\Framework\TestCase;

class TransactionFactoryTest extends TestCase
{
    /**
     * @var TransactionFactory
     */
    private $service;
    /**
     * @var Transaction
     */
    private $transaction;

    public function setUp()
    {
        $this->service = new TransactionFactory();
        $this->transaction = new Transaction(
            1,
            new DateTime('2018-01-01'),
            1,
            'natural',
            'cash_in',
            100,
            'EUR'
        );
    }

    public function testBuildFromData()
    {
        $this->assertEquals($this->transaction, $this->service->buildFromData([
            'id' => 1,
            'date' => '2018-01-01',
            'user_id' => 1,
            'user_type' => 'natural',
            'transaction_type' => 'cash_in',
            'amount' => 100,
            'currency' => 'EUR'
        ]));
    }
}
