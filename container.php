<?php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

$container = new ContainerBuilder();

$container->register('config', \Paysera\Classes\Config::class);

$container->register('commission_service', \Paysera\Services\Commission\Commission::class)
    ->setArguments([
        new Reference('cash_in_service'),
        new Reference('cash_out_service'),
    ]);

$container->register('cash_in_service', \Paysera\Services\Transaction\CashIn::class)
    ->setArguments([
        new Reference('config'),
    ]);

$container->register('cash_out_service', \Paysera\Services\Transaction\CashOut::class)
    ->setArguments([
        new Reference('config'),
    ]);

$container->register('csv_validator_service', \Paysera\Services\CsvValidator::class)
    ->setArguments([
        new Reference('config'),
    ]);

$container->register('file_manager_service', \Paysera\Services\FileManager::class)
    ->setArguments([
        new Reference('config'),
        new Reference('csv_validator_service'),
    ]);

$container->register('transaction_factory_service', \Paysera\Services\Transaction\TransactionFactory::class);

$container->register('user_factory_service', \Paysera\Services\User\UserFactory::class);