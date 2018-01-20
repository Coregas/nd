<?php
namespace Paysera\Services\Commission;

use AppConfig\Config;
use Paysera\Classes\Transaction;
use Paysera\Classes\User;
use Paysera\Services\Transaction\CashIn;
use Paysera\Services\Transaction\CashOut;

class Commission
{
    private $cashInConfig;
    private $cashOutConfig;
    private $config;
    /**
     * @var CashIn
     */
    private $cashInService;
    /**
     * @var CashOut
     */
    private $cashOutService;

    public function __construct(
        Config $config,
        CashIn $cashInService,
        CashOut $cashOutService
    ){
        $this->cashInConfig = $config->getCashInConfig();
        $this->cashOutConfig = $config->getCashOutConfig();
        $this->config = $config;
        $this->cashInService = $cashInService;
        $this->cashOutService = $cashOutService;
    }

    /**
     * @param User $user
     * @return array
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
     * @return array
     */
    public function cashInsCommissions($transactions)
    {
        foreach ($transactions as $transaction) {
            $transaction->setCommissionFee($this->cashInService->commissionFee($transaction));
        }
        return $transactions;
    }

    /**
     * @param array $cashOuts
     * @param string $userType
     * @return mixed
     */
    public function cashOutsCommissions($cashOuts, $userType)
    {
        return $this->cashOutService->cashOutsCommissions($cashOuts, $userType);
    }
}