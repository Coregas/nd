<?php
namespace Paysera\Tests\Transaction;

use Paysera\Classes\Transaction;
use Paysera\Classes\User;
use DateTime;
use Paysera\Services\Commission\Commission;
use Paysera\Services\Transaction\CashIn;
use Paysera\Services\Transaction\CashOut;
use PHPUnit\Framework\TestCase;

class CommissionTest extends TestCase
{
    /**
     * @var Commission
     */
    private $service;

    private $cashInServiceMock;

    private $cashOutServiceMock;
    /**
     * @var Transaction
     */
    private $cashInTrans;
    /**
     * @var Transaction
     */
    private $cashOutTrans;

    public function setUp()
    {
        $this->cashInServiceMock = $this->getMockBuilder(CashIn::class)->disableOriginalConstructor()->getMock();
        $this->cashOutServiceMock = $this->getMockBuilder(CashOut::class)->disableOriginalConstructor()->getMock();
        $this->service = new Commission(
            $this->cashInServiceMock,
            $this->cashOutServiceMock
        );
        $this->cashInTrans = new Transaction(
          1,
          new DateTime('2018-01-01'),
          1,
          'natural',
          'cash_in',
          1000,
          'EUR'
        );

        $this->cashOutTrans = new Transaction(
            2,
            new DateTime('2018-01-01'),
            1,
            'natural',
            'cash_out',
            1000,
            'EUR'
        );
    }

    public function testCashInUser()
    {
        $user = new User(1, 'natural');
        $user->setTransactions([
            $this->cashInTrans,
            $this->cashOutTrans
        ]);
        $this->cashInServiceMock->expects($this->once())
            ->method('commissionFee')
            ->willReturn($this->cashInTrans);

        $this->cashOutServiceMock->expects($this->once())
            ->method('cashOutsCommissions')
            ->willReturn([$this->cashOutTrans]);

        $this->assertInternalType('array', $this->service->processUserTransactions($user));

    }


}
