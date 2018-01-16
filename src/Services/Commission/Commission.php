<?php
namespace Paysera\Services\Commission;

use AppConfig\Config;
use Paysera\Classes\Transaction;
use Paysera\Classes\User;

class Commission
{
    private $cashInConfig;
    private $cashOutConfig;

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
    private function cashOutLegalUser($user)
    {
        foreach ($user->getTransactions() as $transaction) {
            $transaction->setCommissionFee($this->cashOutLegalCommisions($transaction));
        }
    }

    private function cashOutNaturalUser($user)
    {

    }

    /**
     * @param Transaction $transaction
     * @return float|mixed
     */
    private function cashOutLegalCommisions($transaction)
    {
        $commissionFee = round($transaction->getAmount() / 100 * $this->cashOutConfig['legal']['commission_fee_percent'], 2, PHP_ROUND_HALF_UP);
        $minCommissionFee = $this->getMInCashOutLegalFee($transaction->getCurrency());
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
    private function getMInCashOutLegalFee($currency)
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
}