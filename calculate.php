<?php
use Paysera\Services\FileManager;
use AppConfig\Config;
use Paysera\Services\CsvValidator;
use Paysera\Services\Transaction\TransactionFactory;
use Paysera\Services\User\UserFactory;
use Paysera\Services\Commission\Commission;

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
    $csvValidator = new CsvValidator($config);
    $fileManager = new FileManager($config, $csvValidator);
    $fileData = $fileManager->readFile($fileName);

    $allTransactions = [];
    $transactionFactory = new TransactionFactory();
    $userFactory = new UserFactory();

    foreach ($fileData as $row) {
        $allTransactions[] = $transactionFactory->buildFromData($row);
    }
    $users = $userFactory->buildFromTransactions($allTransactions);

    $commissionService = new Commission($config);

    foreach ($users as $user) {
        $commissionService->cashOutCommissions($user);
        foreach ($user->getTransactions() as $transaction) {
            if($transaction->getType() == 'cash_in') {
                $transaction->setCommissionFee($commissionService->cashInCommissions($transaction));
            }

        }
    }
    dump($users);

}


?>

