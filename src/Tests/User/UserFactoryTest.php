<?php
namespace Paysera\Tests\Transaction;

use Paysera\Classes\User;
use PHPUnit\Framework\TestCase;
use Paysera\Services\User\UserFactory;
use Paysera\Classes\Transaction;
use DateTime;

class UserFactoryTest extends TestCase
{
    /**
     * @var UserFactory
     */
    private $service;
    /**
     * @var Transaction
     */
    private $transaction;
    /**
     * @var User
     */
    private $user;

    public function setUp()
    {
        $this->service = new UserFactory();
        $this->user = new User(
            1,
            'natural'
        );
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
        $this->assertEquals($this->user, $this->service->buildFromData([
            'id' => 1,
            'type' => 'natural',
        ]));
    }

    public function testBuildFromTransactions()
    {
        $user = new User(
            1,
            'natural'
        );
        $user->setTransactions([$this->transaction]);
        $this->assertEquals([$user], $this->service->buildFromTransactions([$this->transaction]));
    }
}
