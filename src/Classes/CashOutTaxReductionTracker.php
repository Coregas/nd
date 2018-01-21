<?php
namespace Paysera\Classes;

class CashOutTaxReductionTracker
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var int
     */
    private $year;
    /**
     * @var int
     */
    private $week;
    /**
     * @var int
     */
    private $untaxedTransCount;
    /**
     * @var array
     */
    private $untaxedAmount;

    public function __construct(
        Config $config,
        int $year,
        int $week
    ) {
        $this->config = $config->getCashOutConfig();
        $this->year = $year;
        $this->week = $week;

        $this->untaxedTransCount = $this->config['natural']['untaxed_transaction_count'];
        foreach ($this->config['currency_types'] as $currency) {
            $this->untaxedAmount[$currency] = $this->config['natural']['week_max_untaxed_amount'][$currency];
        }
    }

    /**
     * @return int
     */
    public function getUntaxedTransCount() : int
    {
        return $this->untaxedTransCount;
    }

    /**
     * @param string $currency
     * @return float
     */
    public function getUntaxedAmountByCurrency(string $currency) : float
    {
        return $this->untaxedAmount[$currency];
    }

    /**
     * @param string $currency
     * @return bool
     */
    public function isLeftUntaxedAmount(string $currency) : bool
    {
        return $this->getUntaxedAmountByCurrency($currency) > 0 ? true : false;
    }

    /**
     * @param Transaction $trans
     * @return bool
     */
    public function isUntaxedAmountGreaterThenTransAmount(Transaction $trans) : bool
    {
        if ($this->getUntaxedAmountByCurrency($trans->getCurrency()) > $trans->getAmount()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Transaction $trans
     */
    public function subtractTransFromUntaxedAmount(Transaction $trans)
    {
        $this->updateAvailableUntaxedAmounts($trans);
        $this->reduceUntaxableTransCount();
    }

    public function zeroUntaxedAmounts()
    {
        foreach ($this->untaxedAmount as &$amount) {
            $amount = 0;
        }
    }

    private function reduceUntaxableTransCount()
    {
        $this->untaxedTransCount--;
    }

    /**
     * @param Transaction $trans
     */
    private function updateAvailableUntaxedAmounts(Transaction $trans)
    {
        switch ($trans->getCurrency()) {
            case 'EUR':
                $this->updateAvailableUntaxedAmountFromEur($trans);
                break;
            case 'USD':
                $this->updateAvailableUntaxedAmountFromUsd($trans);
                break;
            case 'JPY':
                $this->updateAvailableUntaxedAmountFromJpy($trans);
                break;
        }
    }

    /**
     * @param Transaction $trans
     */
    private function updateAvailableUntaxedAmountFromEur($trans)
    {
        $leftAmount = $this->untaxedAmount[$trans->getCurrency()] - $trans->getAmount();
        $this->untaxedAmount['EUR'] = $leftAmount;
        $this->untaxedAmount['USD'] = $leftAmount / $this->config['EUR_USD_rate'];
        $this->untaxedAmount['JPY'] = $leftAmount / $this->config['EUR_JPY_rate'];
    }

    /**
     * @param Transaction $trans
     */
    private function updateAvailableUntaxedAmountFromUsd($trans)
    {
        $leftAmount = $this->untaxedAmount[$trans->getCurrency()] - $trans->getAmount();
        $this->untaxedAmount['USD'] = $leftAmount;
        $this->untaxedAmount['EUR'] = $leftAmount * $this->config['EUR_USD_rate'];
        $this->untaxedAmount['JPY'] = $leftAmount * $this->config['EUR_USD_rate'] / $this->config['EUR_JPY_rate'];
    }

    /**
     * @param Transaction $trans
     */
    private function updateAvailableUntaxedAmountFromJpy($trans)
    {
        $leftAmount = $this->untaxedAmount[$trans->getCurrency()] - $trans->getAmount();
        $this->untaxedAmount['JPY'] = $leftAmount;
        $this->untaxedAmount['EUR'] = $leftAmount * $this->config['EUR_JPY_rate'];
        $this->untaxedAmount['USD'] = $leftAmount * $this->config['EUR_JPY_rate'] / $this->config['EUR_USD_rate'];
    }
}
