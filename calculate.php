<?php
use Paysera\Services\FileManager;
use AppConfig\Config;
use Paysera\Services\CsvValidator;
use Paysera\Services\Transaction\TransactionFactory;
use Paysera\Services\User\UserFactory;
use Paysera\Services\Commission\Commission;
use Paysera\Services\Transaction\CashIn;
use Paysera\Services\Transaction\CashOut;
use Paysera\Classes\User;

require __DIR__ . '/vendor/autoload.php';

    try {
        if (isset($argv[1])) {
            init($argv[1]);
        } else {
            throw new Exception('No file specified');
        }
    } catch (Exception $e) {
        fwrite(STDOUT, $e->getMessage());
    }

/**
 * @param string $fileName
 */
function init($fileName)
{
    $config = new Config();
    $cashInService = new CashIn($config);
    $cashOutService = new CashOut($config);
    $csvValidator = new CsvValidator($config);
    $fileManager = new FileManager($config, $csvValidator);
    $fileData = $fileManager->readFile($fileName);
    $allTransactions = [];
    $transactionFactory = new TransactionFactory();
    $userFactory = new UserFactory();
    $commissionService = new Commission($config, $cashInService, $cashOutService);


    foreach ($fileData as $row) {
        $allTransactions[] = $transactionFactory->buildFromData($row);
    }
    $users = $userFactory->buildFromTransactions($allTransactions);

    foreach ($users as $user) {
        $user->setTransactions($commissionService->processUserTransactions($user));
    }
    getCommissionData($users);
}

function printData($commissionData) {
    foreach ($commissionData as $fee) {
        fwrite(STDOUT, round($fee, 2, PHP_ROUND_HALF_UP) . PHP_EOL);
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
    dump($commissionData);
}

?>

