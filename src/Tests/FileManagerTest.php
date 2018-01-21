<?php
namespace Paysera\Tests\Transaction;

use Paysera\Services\FileManager;
use PHPUnit\Framework\TestCase;
use Paysera\Classes\Config;
use Paysera\Services\CsvValidator;

class FileManagerTest extends TestCase
{
    private $config;

    private $csvValidatorServiceMock;

    private $service;

    /**
     * @var array
     */
    private $dataArray = [];

    public function setUp()
    {
        $this->config = new Config();

        $this->csvValidatorServiceMock = $this->getMockBuilder(CsvValidator::class)->disableOriginalConstructor()->getMock();


        $this->service = new FileManager(
            $this->config,
            $this->csvValidatorServiceMock
        );

        $this->dataArray = [array('2015-01-01','1','natural','cash_out','1200.00','EUR'
        )];
    }
    /**
     * @expectedException \Exception
     */
    public function testReadFile()
    {
        $this->service->readFile('xxx.xxxx');
    }

}