<?php
namespace Paysera\Services\Commission;

use Paysera\Classes\Transaction;
use Paysera\Classes\User;
use Paysera\Services\Transaction\CashIn;
use Paysera\Services\Transaction\CashOut;

class Commission
{
    /**
     * @var CashIn
     */
    private $cashInService;
    /**
     * @var CashOut
     */
    private $cashOutService;

    public function __construct(
        CashIn $cashInService,
        CashOut $cashOutService
    ) {
        $this->cashInService = $cashInService;
        $this->cashOutService = $cashOutService;
    }

    /**
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function processUserTransactions($user)
    {

        $cashIns = $user->getTransactionsByType('cash_in');
        if (!empty($cashIns)) {
            $cashIns = $this->cashInsCommissions($cashIns);
        }

        $cashOuts = $user->getTransactionsByType('cash_out');
        if (!empty($cashOuts)) {
            $cashOuts = $this->cashOutsCommissions($cashOuts, $user->getUserType());
        }

        return array_merge($cashIns, $cashOuts);
    }

    /**
     * @param Transaction[] $transactions
     * @return mixed
     * @throws \Exception
     */
    private function cashInsCommissions($transactions)
    {
        foreach ($transactions as $transaction) {
            $transaction->setCommissionFee($this->cashInService->commissionFee($transaction));
        }
        return $transactions;
    }

    /**
     * @param $cashOuts
     * @param $userType
     * @return mixed|null
     * @throws \Exception
     */
    private function cashOutsCommissions($cashOuts, $userType)
    {
        return $this->cashOutService->cashOutsCommissions($cashOuts, $userType);
    }
}
