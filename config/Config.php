<?php
namespace AppConfig;

class Config {


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
                'transaction_types' => ['cash_in', 'cash_out'],
                'amount_format' => '/^-?(?:\d+|\d*\.\d+)$/',
                'amount_format_examples' => ['123','13.37'],
                'currency_format' => '/[A-Z]{3}$/',
                'currency_types' => ['EUR', 'USD', 'JPY'],
                'csv_file_headers' => ['date', 'user_id', 'user_type', 'operation', 'amount', 'currency']
        );
    }
}