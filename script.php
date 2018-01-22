<?php
use Paysera\Classes\User;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/container.php';

    try {
        if (isset($argv[1])) {
            init($argv[1], $container);
        } else {
            throw new Exception('No file specified');
        }
    } catch (Exception $e) {
        fwrite(STDOUT, $e->getMessage());
    }

/**
 * @param $fileName
 * @param $container
 */
function init($fileName, $container)
{
    $fileManager = $container->get('file_manager_service');
    $transactionFactory = $container->get('transaction_factory_service');
    $userFactory = $container->get('user_factory_service');
    $commissionService = $container->get('commission_service');

    try {
        $fileData = $fileManager->readFile($fileName);
        $allTransactions = [];


        foreach ($fileData as $row) {
            $allTransactions[] = $transactionFactory->buildFromData($row);
        }
        $users = $userFactory->buildFromTransactions($allTransactions);

        foreach ($users as $user) {
            $user->setTransactions($commissionService->processUserTransactions($user));
        }
        getCommissionData($users);

    } catch (\Exception $e) {
        fwrite(STDOUT, $e->getMessage());
        die();
    }
}
/**
 * @param User[] $users
 */
function getCommissionData($users) {
    $transactions = [];
    $commissionData = [];

    foreach ($users as $user) {
        $transactions = array_merge($transactions, $user->getTransactions());
    }

    foreach ($transactions as $transaction) {
        $commissionData[$transaction->getId()] = $transaction->getCommissionFee();
    }
    ksort($commissionData);

    printData($commissionData);
}

/**
 * @param array $commissionData
 */
function printData($commissionData) {
    foreach ($commissionData as $fee) {
        fwrite(STDOUT, number_format((float)$fee, 2, '.', '') . PHP_EOL);
    }
}
