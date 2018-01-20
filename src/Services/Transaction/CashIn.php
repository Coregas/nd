<?php
namespace Paysera\Services\Transaction;
use AppConfig\Config;
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

    public function commissionFee(Transaction $trans)
    {
        $commissionFee = $trans->getAmount() / 100 * $this->config['commission_fee_percent'];
        $maxCommissionFee = $this->getMaxFee($trans->getCurrency());
        $commissionFee = $commissionFee > $maxCommissionFee ? $maxCommissionFee : $commissionFee;

        return $commissionFee;
    }

    /**
     * @param $currency
     * @return mixed
     */
    private function getMaxFee($currency)
    {
        try {
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
                    throw new \Exception('Unhandled cash_in currency, given:' . $currency);
                    break;
            }
        } catch (\Exception $e) {
            fwrite(STDOUT, $e->getMessage());
            die();
        }
    }
}