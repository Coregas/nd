<?php
use Paysera\Services\FileManager;
use AppConfig\Config;
use Paysera\Services\CsvValidator;
use Paysera\Services\Transaction\TransactionFactory;
use Paysera\Services\User\UserFactory;

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
    dump($users);

}


?>

