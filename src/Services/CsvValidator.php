<?php
namespace Paysera\Services;

use Paysera\Classes\Config;

class CsvValidator
{
    /**
     * @var array
     */
    private $csvConfig;

    public function __construct(
        Config $config
    ) {
        $this->csvConfig = $config->getCsvConfig();
    }

    /**
     * @param array $row
     * @return bool
     * @throws \Exception
     */
    public function validateColumnCount(array $row) : bool
    {
        $columnCount = count($row);

        if ($columnCount == $this->csvConfig['column_count']) {
            return true;
        } else {
            throw new \Exception(
                'bad column count, given:' .
                $columnCount .
                ' set in csv config:' .
                $this->csvConfig['column_count'] .
                PHP_EOL
            );
        }
    }

    /**
     * @param string $date
     * @return string
     * @throws \Exception
     */
    public function validateDate(string $date) : string
    {
        if (preg_match($this->csvConfig['date_format'], $date)) {
            return $date;
        } else {
            throw new \Exception(
                'badly formatted date, given:' .
                $date .
                ' format EXAMPLE set in csv config:' .
                $this->csvConfig['date_format_example'] .
                PHP_EOL
            );
        }
    }

    /**
     * @param string $userId
     * @return string
     * @throws \Exception
     */
    public function validateUserId(string $userId) : string
    {
        if (preg_match($this->csvConfig['user_id_format'], $userId)) {
            return $userId;
        } else {
            throw new \Exception(
                'badly formatted user_id, given:' .
                $userId .
                ' user_id format EXAMPLE set in csv config:' .
                $this->csvConfig['user_id_format_example'] .
                PHP_EOL
            );
        }
    }

    /**
     * @param string $userType
     * @return string
     * @throws \Exception
     */
    public function validateUserType(string $userType) : string
    {
        if (in_array($userType, $this->csvConfig['user_types'])) {
            return $userType;
        } else {
            throw new \Exception(
                'user_type not found, given: ' .
                $userType .
                ' available user_types set in csv config: ' .
                $this->csvConfig['user_types_example'] .
                PHP_EOL
            );
        }
    }

    /**
     * @param string $operationType
     * @return string
     * @throws \Exception
     */
    public function validateTransactionType(string $operationType) : string
    {
        if (in_array($operationType, $this->csvConfig['transaction_types'])) {
            return $operationType;
        } else {
            throw new \Exception(
                'operation_type not found, given:' .
                $operationType .
                ' available operation_type set in csv config:' .
                $this->csvConfig['transaction_types_example'] .
                PHP_EOL
            );
        }
    }

    /**
     * @param string $amount
     * @return string
     * @throws \Exception
     */
    public function validateAmount(string $amount) : string
    {
        if (preg_match($this->csvConfig['amount_format'], $amount)) {
            return $amount;
        } else {
            throw new \Exception(
                'badly formatted amount, given:' .
                $amount .
                ' available amount_formats set in csv config:' .
                $this->csvConfig['amount_format_examples'] .
                PHP_EOL
            );
        }
    }

    /**
     * @param string $currency
     * @return string
     * @throws \Exception
     */
    public function validateCurrency(string $currency) : string
    {
        if (strlen($currency) == $this->csvConfig['currency_format_length'] &&
            preg_match($this->csvConfig['currency_format'], $currency)) {
            if (in_array($currency, $this->csvConfig['currency_types'])) {
                return $currency;
            } else {
                throw new \Exception(
                    'badly formatted currency, given:' .
                    $currency .
                    ' available currency_format set in csv config:' .
                    $this->csvConfig['available_currency_types'] .
                    PHP_EOL
                );
            }
        } else {
            throw new \Exception(
                'currency_type not found, given:' .
                $currency .
                ' available currency_types set in csv config:' .
                $this->csvConfig['available_currency_types'] .
                PHP_EOL
            );
        }
    }
}
