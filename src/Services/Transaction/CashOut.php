<?php
namespace Paysera\Services\Transaction;

use AppConfig\Config;
use Paysera\Classes\Transaction;
use Paysera\Classes\CashOutTaxReductionTracker;

class CashOut
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Config
     */
    private $fullConfig;
    /**
     * @var CashOutTaxReductionTracker
     */
    private $transWeekTracker;

    public function __construct(
       Config $config
    ){
        $this->config = $config->getCashOutConfig();
        $this->fullConfig = $config;
    }

    /**
     * @param $cashOuts
     * @param $userType
     * @return mixed|null
     * @throws \Exception
     */
    public function cashOutsCommissions($cashOuts, $userType)
    {
        if ($userType == 'legal') {
            return $this->cashOutLegalUser($cashOuts);
        }

        if ($userType == 'natural') {
           return $this->cashOutNaturalUser($cashOuts);
        }

        throw new \Exception('Bad user Type given');
    }

    /**
     * @param $cashOuts
     * @return mixed
     * @throws \Exception
     */
    private function cashOutNaturalUser($cashOuts)
    {
        if (count($cashOuts) > 1) {
            return $this->processNaturalUserCashOutTransactions($cashOuts);
        } else {
            return $this->processSingleNaturalUserCashOutTransaction($cashOuts);
        }
    }

    /**
     * @param Transaction[] $transactions
     * @return mixed
     */
    private function processNaturalUserCashOutTransactions($transactions)
    {
        foreach ($transactions as $firstTrans) {
            if (is_null($firstTrans->getCommissionFee())) {
                $this->transWeekTracker = new CashOutTaxReductionTracker($this->fullConfig, $firstTrans->getYear(), $firstTrans->getWeek());
                foreach ($transactions as $secondTrans) {
                    if ($this->areTransactionsOnSameWeek($firstTrans, $secondTrans)) {
                        if (is_null($secondTrans->getCommissionFee())) {
                           $this->naturalCashOutTransactionTaxReduction($secondTrans);
                        }
                    }
                }
            }
        }

        return $transactions;
    }
    /**
     * @param Transaction $transaction
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
            $leftTransAmount = $trans->getAmount() - $this->transWeekTracker->getUntaxedAmountByCurrency($trans->getCurrency());
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
        $commissionFee = $transAmount / 100 * $this->config['natural']['commission_fee_percent'];

        return round($commissionFee, 2, PHP_ROUND_HALF_UP);;
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
     * @return mixed
     * @throws \Exception
     */
    private function processSingleNaturalUserCashOutTransaction($transactions)
    {
        $commissionFee = $transactions[0]->getAmount() / 100 * $this->config['natural']['commission_fee_percent'];
        $maxTaxFreeCashOut = $this->getMaxCashOutNaturalFreeFee($transactions[0]->getCurrency());

        if ($commissionFee > $maxTaxFreeCashOut) {
            $commissionFee -= $maxTaxFreeCashOut;
            $commissionFee = round($commissionFee, 2, PHP_ROUND_HALF_UP);
            $transactions[0]->setCommissionFee($commissionFee);
        } else {
            $transactions[0]->setCommissionFee(0);
        }

        return $transactions;
    }

    /**
     * @param $cashOuts
     * @return mixed
     * @throws \Exception
     */
    private function cashOutLegalUser($cashOuts)
    {
        foreach ($cashOuts as $transaction) {
            $transaction->setCommissionFee($this->cashOutLegalCommissions($transaction));
        }
        return $cashOuts;
    }


    /**
     * @param $transaction
     * @return float
     * @throws \Exception
     */
    private function cashOutLegalCommissions($transaction)
    {
        $commissionFee = $transaction->getAmount() / 100 * $this->config['legal']['commission_fee_percent'];
        $minCommissionFee = $this->getMinCashOutLegalFee($transaction->getCurrency());
        $commissionFee = $commissionFee < $minCommissionFee ? $minCommissionFee : $commissionFee;

        return round($commissionFee, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * @param $currency
     * @return mixed
     * @throws \Exception
     */
    private function getMaxCashOutNaturalFreeFee($currency)
    {
        switch ($currency) {
            case 'EUR':
                return $this->config['natural']['week_max_untaxed_amount']['EUR'];
                break;
            case 'USD':
                return $this->config['natural']['week_max_untaxed_amount']['USD'];
                break;
            case 'JPY':
                return $this->config['natural']['week_max_untaxed_amount']['JPY'];
                break;
            default:
                throw new \Exception('Unhandled cash_out legal user currency ' . $currency);
                break;
        }
    }

    /**
     * @param $currency
     * @return mixed
     * @throws \Exception
     */
    private function getMinCashOutLegalFee($currency)
    {
        switch ($currency) {
            case 'EUR':
                return $this->config['legal']['fee_min_EUR'];
                break;
            case 'USD':
                return $this->config['legal']['fee_min_USD'];
                break;
            case 'JPY':
                return $this->config['legal']['fee_min_JPY'];
                break;
            default:
                throw new \Exception('Unhandled cash_out legal user currency ' . $currency);
                break;
        }
    }
}