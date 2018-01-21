<?php
namespace Paysera\Services\Transaction;

use Paysera\Classes\Config;
use Paysera\Classes\Transaction;

class CashIn
{
    /**
     * @var array
     */
    private $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config->getCashInConfig();
    }

    /**
     * @param Transaction $trans
     * @return float
     * @throws \Exception
     */
    public function commissionFee(Transaction $trans)
    {
        $commissionFee = $trans->getAmount() / 100 * $this->config['commission_fee_percent'];
        $maxCommissionFee = $this->getMaxFee($trans->getCurrency());
        $commissionFee = $commissionFee > $maxCommissionFee ? $maxCommissionFee : $commissionFee;

        return round($commissionFee, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * @param $currency
     * @return mixed
     * @throws \Exception
     */
    private function getMaxFee($currency)
    {
        switch ($currency) {
            case 'EUR':
                return $this->config['fee_max_EUR'];
                break;
            case 'USD':
                return $this->config['fee_max_USD'];
                break;
            case 'JPY':
                return $this->config['fee_max_JPY'];
                break;
            default:
                throw new \Exception('Unhandled cash_out legal user currency ' . $currency);
                break;
        }
    }
}
