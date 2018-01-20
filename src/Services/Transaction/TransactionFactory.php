<?php
namespace Paysera\Services\Transaction;

use Paysera\Classes\Transaction;
use DateTime;

class TransactionFactory
{
    public function buildFromData($data)
    {
        return new Transaction(
            intval($data['id']),
          new DateTime($data['date']),
          intval($data['user_id']),
          $data['user_type'],
          $data['transaction_type'],
          floatval($data['amount']),
          $data['currency']
        );
    }
}