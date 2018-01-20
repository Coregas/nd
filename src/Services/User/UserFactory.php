<?php
namespace Paysera\Services\User;

use Paysera\Classes\Transaction;
use Paysera\Classes\User;

class UserFactory
{
    public function buildFromData($data)
    {
        return new User(
          $data['id'],
          $data['type']
        );
    }

    /**
     * @param Transaction[] $transactions
     * @return array
     */
    public function buildFromTransactions(array $transactions) {
        $uniqueUsers = $this->findUniqueUserIds($transactions);
        dump($uniqueUsers);
        $users = [];

        foreach ($uniqueUsers as $key => $uniqueUser) {
            $userTransactions = $this->findTransactionsByUserId($uniqueUser['id'], $transactions);
            $users[$key] = $this->buildFromData($uniqueUser);
            $users[$key]->setTransactions($userTransactions);
        }

        return $users;

    }

    /**
     * @param int $userId
     * @param Transaction[] $transactions
     * @return Transaction[]
     */
    private function findTransactionsByUserId($userId, $transactions)
    {
        $userTransactions = [];

        foreach ($transactions as $transaction)
        {
            if ($transaction->getUserId() == $userId) {
                $userTransactions[] = $transaction;
            }
        }
        return $userTransactions;
    }

    /**
     * @param Transaction[] $transactions
     * @return array
     */
    private function findUniqueUserIds($transactions)
    {
        $uniqueUsers = [];

        foreach ($transactions as $key => $transaction) {
            if(!in_array($transaction->getUserId(), array_column($uniqueUsers, 'id'))) {
                $uniqueUsers[$key]['id'] = $transaction->getUserId();
                $uniqueUsers[$key]['type'] = $transaction->getUserType();
            }
        }

        return array_values($uniqueUsers);
    }
}