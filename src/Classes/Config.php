<?php
namespace Paysera\Classes;

class Config {

    private $eurUsdRate = 1 / 1.1497;
    private $eurJpyRate = 1 / 129.53;
    private $availableCurrencies = ['EUR', 'USD', 'JPY'];
    /**
     * @return array
     */
    public function getCsvConfig()
    {
        return array(
                'column_count' => 6,
                'date_format' => '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/',
                'date_format_example' => 'EUR',
                'user_id_format' => '/^\d+$/',
                'user_id_format_example' => '1337',
                'user_types' => ['natural', 'legal'],
                'user_types_example' =>'natural, legal',
                'transaction_types' => ['cash_in', 'cash_out'],
                'transaction_types_example' => 'cash_in, cash_out',
                'amount_format' => '/^(?:[1-9]\d*|0)?(?:\.\d+)?$/',
                'amount_format_examples' => 'positive floats',
                'currency_format' => '/[A-Z]{3}$/',
                'currency_format_length' => 3,
                'available_currency_types' => implode(', ', $this->availableCurrencies),
                'currency_types' => $this->availableCurrencies,
                'csv_file_headers' => ['date', 'user_id', 'user_type', 'operation', 'amount', 'currency']
        );
    }

    public function getCashInConfig()
    {
        return array(
            'commission_fee_percent' => 0.03,
            'fee_max_EUR' => 5.00,
            'fee_max_USD' => 5 / $this->eurUsdRate,
            'fee_max_JPY' => 5 / $this->eurJpyRate,
            'currency_types' => $this->availableCurrencies
        );
    }

    public function getCashOutConfig()
    {
        return array(
            'currency_types' => $this->availableCurrencies,
            'EUR_USD_rate' => $this->eurUsdRate,
            'EUR_JPY_rate' => $this->eurJpyRate,
            'legal' => [
                'commission_fee_percent' => 0.3,
                'fee_min_EUR' => 0.50,
                'fee_min_USD' => 0.50 / $this->eurUsdRate,
                'fee_min_JPY' => 0.50 / $this->eurJpyRate,
            ],
            'natural' => [
                'commission_fee_percent' => 0.3,
                'week_max_untaxed_amount' =>[
                    'EUR' => 1000.00,
                    'USD' => 1000.00 / $this->eurUsdRate,
                    'JPY' => 1000.00 / $this->eurJpyRate,
                ],
                'untaxed_transaction_count' => 3
            ]
        );
    }
}