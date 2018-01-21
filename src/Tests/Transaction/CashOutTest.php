<?php
namespace Paysera\Tests\Transaction;

use Paysera\Classes\Config;
use Paysera\Classes\Transaction;
use Paysera\Classes\User;
use Paysera\Services\Transaction\CashOut;
use \PHPUnit\Framework\TestCase;
use DateTime;

class CashOutTest extends TestCase
{
    private $config;

    /**
     * @var \Paysera\Services\Transaction\CashOut;
     */
    private $service;
    /**
     * @var Transaction
     */
    private $naturalTransactionEur;
    /**
     * @var Transaction
     */
    private $naturalTransactionUsd;
    /**
     * @var Transaction
     */
    private $naturalTransactionJpy;
    /**
     * @var Transaction
     */
    private $legalTransactionJpy;
    /**
     * @var Transaction
     */
    private $legalTransactionEur;
    /**
     * @var Transaction
     */
    private $legalTransactionUsd;
    /**
     * @var Transaction
     */
    private $transactionFalseCurrency;
    /**
     * @var User
     */
    private $naturalUser;
    /**
     * @var User
     */
    private $legalUser;

    public function setUp()
    {
        $this->config = new Config();

        $this->service = new CashOut($this->config);

        $this->naturalTransactionEur = new Transaction(
            1,
            new DateTime('2018-01-01'),
            1,
            'natural',
            'cash_out',
            999.99,
            'EUR'
        );

        $this->naturalTransactionUsd = new Transaction(
            1,
            new DateTime('2018-01-02'),
            1,
            'natural',
            'cash_out',
            100000000000,
            'USD'
        );

        $this->naturalTransactionJpy = new Transaction(
            1,
            new DateTime('2018-04-01'),
            1,
            'natural',
            'cash_out',
            9800000,
            'JPY'
        );

        $this->legalTransactionJpy = new Transaction(
            1,
            new DateTime('2018-01-01'),
            1,
            'legal',
            'cash_out',
            30000000,
            'JPY'
        );
        $this->legalTransactionEur = new Transaction(
            1,
            new DateTime('2018-01-01'),
            1,
            'legal',
            'cash_out',
            10,
            'EUR'
        );
        $this->legalTransactionUsd = new Transaction(
            1,
            new DateTime('2018-01-01'),
            1,
            'legal',
            'cash_out',
            1000,
            'USD'
        );

        $this->transactionFalseCurrency = new Transaction(
            1,
            new DateTime('2018-01-01'),
            1,
            'legal',
            'cash_out',
            1000,
            'aaa'
        );
        $this->naturalUser = new User(1, 'natural');
        $this->naturalUser->setTransactions([
            $this->naturalTransactionEur,
            $this->naturalTransactionJpy,
            $this->naturalTransactionUsd
        ]);
        $this->legalUser = new User(1, 'legal');
        $this->legalUser->setTransactions([
            $this->legalTransactionJpy,
            $this->legalTransactionEur,
            $this->legalTransactionUsd
        ]);
    }

    public function testLegalUser()
    {
        $this->assertInternalType(
            'array',
            $this->service->cashOutsCommissions(
                [$this->legalTransactionJpy,$this->legalTransactionEur,$this->legalTransactionUsd],
                $this->legalUser->getUserType()
            )
        );
    }

    public function testNaturalUser()
    {
        $this->assertInternalType(
            'array',
            $this->service->cashOutsCommissions(
                [$this->naturalTransactionJpy, $this->naturalTransactionEur, $this->naturalTransactionUsd],
                $this->naturalUser->getUserType()
            )
        );
    }
    public function testNaturalUser2()
    {
        $this->assertInternalType(
            'array',
            $this->service->cashOutsCommissions(
                [$this->naturalTransactionJpy, $this->naturalTransactionUsd, $this->naturalTransactionEur],
                $this->naturalUser->getUserType()
            )
        );
    }

    public function testSingleNaturalTransactionEur()
    {
        $this->assertInternalType(
            'array',
            $this->service->cashOutsCommissions(
                [$this->naturalTransactionEur],
                $this->naturalUser->getUserType()
            )
        );
    }

    public function testSingleNaturalTransactionUsd()
    {
        $this->assertInternalType(
            'array',
            $this->service->cashOutsCommissions(
                [$this->naturalTransactionUsd],
                $this->naturalUser->getUserType()
            )
        );
    }

    public function testSingleNaturalTransactionJpy()
    {
        $this->assertInternalType(
            'array',
            $this->service->cashOutsCommissions(
                [$this->naturalTransactionJpy],
                $this->naturalUser->getUserType()
            )
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testUndefinedUserType()
    {
        $this->service->cashOutsCommissions(
            [$this->naturalTransactionJpy],
            'aaaaa'
        );
    }
    /**
     * @expectedException \Exception
     */
    public function testUndefinedCurrencyNatural()
    {
        $this->service->cashOutsCommissions(
            [$this->transactionFalseCurrency],
            'natural'
        );
    }
    /**
     * @expectedException \Exception
     */
    public function testUndefinedCurrencyLegal()
    {
        $this->service->cashOutsCommissions(
            [$this->transactionFalseCurrency],
            'legal'
        );
    }
}
