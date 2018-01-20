<?php
namespace Paysera\Services\Commission;

use AppConfig\Config;
use Paysera\Classes\Transaction;
use Paysera\Classes\User;

class Commission
{
    private $cashInConfig;
    private $cashOutConfig;
    private $transWeekTracker = [];

    public function __construct(
        Config $config
    ){
        $this->cashInConfig = $config->getCashInConfig();
        $this->cashOutConfig = $config->getCashOutConfig();
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
            $this->setTransWeekTracker($firstTrans);
            foreach ($transactions as $secondTrans) {
                if ($this->areTransactionsOnSameWeek($firstTrans, $secondTrans)) {
                    if (is_null($secondTrans->getCommissionFee())) {
                        $this->naturalCashOutTransactionTaxReduction( $secondTrans);
                    }
                }
            }
        }
    }
    /**
     * @param Transaction $transaction
     * @return mixed
     */
    private function naturalCashOutTransactionTaxReduction( $transaction)
    {

        if ($this->transWeekTracker[$transaction->getYear()][$transaction->getWeek()]['untaxable_transaction_count'] > 0 &&
            $this->availableTransactionTaxReduction($transaction)) {
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
        $availableReduction = $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount'][$trans->getCurrency()];

        if ($availableReduction >= $trans->getAmount()) {
            $this->transWeekTracker[$trans->getYear()][$trans->getWeek()][$trans->getCurrency()] = $availableReduction - $trans->getAmount();
            $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxable_transaction_count']--;
            $this->updateAvailableTaxReductionAmount($trans);
            return 0;
        } else {
        $this->zeroAvailableTaxReductionAmount($trans);
        return $trans->getAmount() - $availableReduction;
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
     * @param Transaction $trans
     */
    private function updateAvailableTaxReductionAmount($trans)
    {
        switch ($trans->getCurrency()) {
            case 'EUR':
                $this->updateAvailableTaxReductionAmountFromEur($trans);
                break;
            case 'USD':
                $this->updateAvailableTaxReductionAmountFromUsd($trans);
                break;
            case 'JPY':
                $this->updateAvailableTaxReductionAmountFromJpy($trans);
                break;
        }
    }

    /**
     * @param Transaction $trans
     */
    private function updateAvailableTaxReductionAmountFromEur($trans)
    {
        $leftAmount = $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['EUR'] - $trans->getAmount();
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['EUR'] = $leftAmount;
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['USD'] = $leftAmount / $this->cashOutConfig['EUR_USD_rate'];
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['JPY'] = $leftAmount / $this->cashOutConfig['EUR_JPY_rate'];
    }
    /**
     * @param Transaction $trans
     */
    private function updateAvailableTaxReductionAmountFromUsd($trans)
    {
        $leftAmount = $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['week_max_untaxed_amount']['USD'] - $trans->getAmount();
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['USD'] = $leftAmount;
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['EUR'] = $leftAmount * $this->cashOutConfig['EUR_USD_rate'];
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['JPY'] = $leftAmount * $this->cashOutConfig['EUR_USD_rate'] / $this->cashOutConfig['EUR_JPY_rate'];
    }
    /**
     * @param Transaction $trans
     */
    private function updateAvailableTaxReductionAmountFromJpy($trans)
    {
        $leftAmount = $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['JPY'] - $trans->getAmount();
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['JPY'] = $leftAmount;
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['EUR'] = $leftAmount * $this->cashOutConfig['EUR_JPY_rate'];
        $this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount']['USD'] = $leftAmount * $this->cashOutConfig['EUR_JPY_rate'] / $this->cashOutConfig['EUR_USD_rate'];
    }
    /**
     * @param $trans
     */
    private function zeroAvailableTaxReductionAmount($trans)
    {
        foreach ($this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount'] as &$amount) {
            $amount = 0;
        }

    }
    /**
     * @param Transaction $trans
     * @return bool
     */
    private function availableTransactionTaxReduction($trans)
    {
        if ($this->transWeekTracker[$trans->getYear()][$trans->getWeek()]['untaxed_amount'][$trans->getCurrency()] > 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @param Transaction $transaction
     */
    private function setTransWeekTracker($transaction)
    {
        if (!isset($this->transWeekTracker[$transaction->getYear()][$transaction->getWeek()])) {
            $this->transWeekTracker[$transaction->getYear()][$transaction->getWeek()]['untaxable_transaction_count'] = $this->cashOutConfig['natural']['untaxed_transaction_count'];
            foreach ($this->cashOutConfig['currency_types'] as $currency) {
                $this->transWeekTracker[$transaction->getYear()][$transaction->getWeek()]['untaxed_amount'][$currency] = $this->cashOutConfig['natural']['week_max_untaxed_amount'][$currency];
            }
        }
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