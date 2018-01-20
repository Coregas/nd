<?php
namespace Paysera\Services\Commission;

use AppConfig\Config;
use Paysera\Classes\Transaction;
use Paysera\Classes\User;
use Paysera\Classes\CashOutTaxReductionTracker;

class Commission
{
    private $cashInConfig;
    private $cashOutConfig;
    private $config;
    /**
     * @var CashOutTaxReductionTracker
     */
    private $transWeekTracker;

    public function __construct(
        Config $config
    ){
        $this->cashInConfig = $config->getCashInConfig();
        $this->cashOutConfig = $config->getCashOutConfig();
        $this->config = $config;
    }

    /**
     * @param $transaction
     * @return float
     */
    public function cashInCommissions($transaction)
    {
        $commissionFee = round($transaction->getAmount() / 100 * $this->cashInConfig['commission_fee_percent'], 2, PHP_ROUND_HALF_UP);
        $maxCommissionFee = $this->getMaxCashInFee($transaction->getCurrency());
        $commissionFee = $commissionFee > $maxCommissionFee ? $maxCommissionFee : $commissionFee;

        return $commissionFee;
    }

    /**
     * @param User $user
     */
    public function cashOutCommissions($user)
    {
        if ($user->getUserType() == 'legal') {
           $this->cashOutLegalUser($user);
        }

        if ($user->getUserType() == 'natural') {
            $this->cashOutNaturalUser($user);
        }
    }

    /**
     * @param User $user
     */
    private function cashOutNaturalUser($user)
    {
        $transactions = $user->getTransactions();

        if (count($transactions) > 1) {
            $this->processNaturalUserCashOutTransactions($transactions);
        } else {
            $this->processSingleNaturalUserCashOutTransaction($transactions);
        }
    }


    private function processNaturalUserCashOutTransactions($transactions)
    {
        foreach ($transactions as $firstTrans) {
            if (is_null($firstTrans->getCommissionFee())) {
                $this->transWeekTracker = new CashOutTaxReductionTracker($this->config, $firstTrans->getYear(), $firstTrans->getWeek());
                foreach ($transactions as $secondTrans) {
                    if ($this->areTransactionsOnSameWeek($firstTrans, $secondTrans)) {
                        if (is_null($secondTrans->getCommissionFee())) {
                            $this->naturalCashOutTransactionTaxReduction($secondTrans);
                        }
                    }
                }
            }
        }
    }
    /**
     * @param Transaction $transaction
     * @return mixed
     */
    private function naturalCashOutTransactionTaxReduction($transaction)
    {

        if ($this->transWeekTracker->getUntaxedTransCount() > 0 &&
            $this->transWeekTracker->isLeftUntaxedAmount($transaction->getCurrency())) {
            $reducedAmount = $this->reduceCashOutTransCommission($transaction);
            if($reducedAmount == 0) {
                $transaction->setCommissionFee(0);
            } else {
                $transaction->setCommissionFee($this->cashOutNaturalCommissions($reducedAmount));
            }
        } else {
            $transaction->setCommissionFee($this->cashOutNaturalCommissions($transaction->getAmount()));
        }

    }

    /**
     * @param Transaction $trans
     * @return int
     */
    private function reduceCashOutTransCommission($trans)
    {
        if ($this->transWeekTracker->isUntaxedAmountGreaterThenTransAmount($trans)) {
            $this->transWeekTracker->subtractTransFromUntaxedAmount($trans);
            return 0;
        } else {
            $leftTransAmount = $trans - $this->transWeekTracker->getUntaxedAmountByCurrency($trans->getCurrency());
            $this->transWeekTracker->zeroUntaxedAmounts();
            return $leftTransAmount;
        }
    }

    /**
     * @param $transAmount
     * @return float|int
     */
    private function cashOutNaturalCommissions($transAmount)
    {
        $commissionFee = $transAmount / 100 * $this->cashOutConfig['natural']['commission_fee_percent'];

        return $commissionFee;
    }

    /**
     * @param Transaction $firstTrans
     * @param Transaction $secondTrans
     * @return bool
     */
    private function areTransactionsOnSameWeek($firstTrans, $secondTrans)
    {
        if ($firstTrans->getDate()->format('W') == $secondTrans->getDate()->format('W')){
            if ($firstTrans->getDate()->format('o') == $secondTrans->getDate()->format('o')) {
                return true;
            }
        }
        return false;
    }
    /**
     * @param Transaction[] $transactions
     * @return float|mixed
     */
    private function processSingleNaturalUserCashOutTransaction($transactions)
    {
        $commissionFee = round($transactions[0]->getAmount() / 100 * $this->cashOutConfig['natural']['commission_fee_percent'], 2, PHP_ROUND_HALF_UP);
        $maxTaxFreeCashOut = $this->getMaxCashOutNaturalFreeFee($transactions[0]->getCurrency());

        if ($commissionFee > $maxTaxFreeCashOut) {
            $transactions[0]->setCommissionFee($commissionFee - $maxTaxFreeCashOut);
        } else {
            $transactions[0]->setCommissionFee(0);
        }
    }

    /**
     * @param User $user
     */
    private function cashOutLegalUser($user)
    {
        foreach ($user->getTransactions() as $transaction) {
            $transaction->setCommissionFee($this->cashOutLegalCommissions($transaction));
        }
    }

    /**
     * @param Transaction $transaction
     * @return float|mixed
     */
    private function cashOutLegalCommissions($transaction)
    {
        $commissionFee = round($transaction->getAmount() / 100 * $this->cashOutConfig['legal']['commission_fee_percent'], 2, PHP_ROUND_HALF_UP);
        $minCommissionFee = $this->getMinCashOutLegalFee($transaction->getCurrency());
        $commissionFee = $commissionFee < $minCommissionFee ? $minCommissionFee : $commissionFee;

        return $commissionFee;
    }
    /**
     * @param $currency
     * @return mixed
     */
    private function getMaxCashInFee($currency)
    {
        try {
            switch ($currency) {
                case 'EUR':
                    return $this->cashInConfig['fee_max_EUR'];
                    break;
                case 'USD':
                    return $this->cashInConfig['fee_max_USD'];
                    break;
                case 'JPY':
                    return $this->cashInConfig['fee_max_JPY'];
                    break;
                default:
                    throw new \Exception('Unhandled cash_in currency ' . $currency);
                    break;
            }
        } catch (\Exception $e) {
            fwrite(STDOUT, $e->getMessage());
            die();
        }
    }

    /**
     * @param $currency
     * @return mixed
     */
    private function getMinCashOutLegalFee($currency)
    {
        try {
            switch ($currency) {
                case 'EUR':
                    return $this->cashOutConfig['legal']['fee_min_EUR'];
                    break;
                case 'USD':
                    return $this->cashOutConfig['legal']['fee_min_USD'];
                    break;
                case 'JPY':
                    return $this->cashOutConfig['legal']['fee_min_JPY'];
                    break;
                default:
                    throw new \Exception('Unhandled cash_out legal user currency ' . $currency);
                    break;
            }
        } catch (\Exception $e) {
            fwrite(STDOUT, $e->getMessage());
            die();
        }
    }

    /**
     * @param $currency
     * @return mixed
     */
    private function getMaxCashOutNaturalFreeFee($currency)
    {
        try {
            switch ($currency) {
                case 'EUR':
                    return $this->cashOutConfig['natural']['week_max_untaxed_amount']['EUR'];
                    break;
                case 'USD':
                    return $this->cashOutConfig['natural']['week_max_untaxed_amount']['USD'];
                    break;
                case 'JPY':
                    return $this->cashOutConfig['natural']['week_max_untaxed_amount']['JPY'];
                    break;
                default:
                    throw new \Exception('Unhandled cash_out legal user currency ' . $currency);
                    break;
            }
        } catch (\Exception $e) {
            fwrite(STDOUT, $e->getMessage());
            die();
        }
    }
}