<?php
namespace Paysera\Tests\Transaction;

use PHPUnit\Framework\TestCase;
use AppConfig\Config;
use Paysera\Services\CsvValidator;

class CsvValidatorTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var CsvValidator
     */
    private $service;

    public function setUp()
    {
        $this->config = new Config();

        $this->service = new CsvValidator($this->config);
    }

    /**
     * @expectedException \Exception
     */
    public function testValidateColumnCount()
    {
        $this->service->validateColumnCount([]);
    }

    public function testValidateColumnCount2()
    {
        $row = [];

        for($i = 1; $i <= $this->config->getCsvConfig()['column_count']; $i++) {
            $row[] = 1;
        }
        $this->assertEquals(true, $this->service->validateColumnCount($row));
    }
    /**
     * @expectedException \Exception
     */
    public function testValidateDate()
    {
      $this->service->validateDate('xxxx');
    }

    public function testValidateDate2()
    {
        $this->assertEquals('2018-01-01', $this->service->validateDate('2018-01-01'));
    }
    /**
     * @expectedException \Exception
     */
    public function testValidateUserId()
    {
        $this->service->validateUserId('a');
    }

    public function testValidateUserId2()
    {
        $this->assertEquals('1', $this->service->validateUserId('1'));
    }
    /**
     * @expectedException \Exception
     */
    public function testValidateUserType()
    {
        $this->service->validateUserType('axxxaxxa');
    }
    public function testValidateUserType2()
    {
        $this->assertEquals(
            $this->config->getCsvConfig()['user_types'][0],
            $this->service->validateUserType($this->config->getCsvConfig()['user_types'][0]));
    }

    /**
     * @expectedException \Exception
     */
    public function testValidateTransactionType()
    {
        $this->service->validateTransactionType('axxxaxxa');
    }

    public function testValidateTransactionType2()
    {
        $this->assertEquals(
            $this->config->getCsvConfig()['transaction_types'][0],
            $this->service->validateTransactionType($this->config->getCsvConfig()['transaction_types'][0]));
    }

    /**
     * @expectedException \Exception
     */
    public function testValidateAmount()
    {
        $this->service->validateAmount('axxxaxxa');
    }

    public function testValidateAmount2()
    {
        $this->assertEquals('100.01', $this->service->validateAmount('100.01'));
    }

    /**
     * @expectedException \Exception
     */
    public function testValidateCurrency()
    {
        $this->service->validateCurrency('PRAISE OUR LORD KEK');
    }

    /**
     * @expectedException \Exception
     */
    public function testValidateCurrency2()
    {
        $this->service->validateCurrency('KEK');
    }

    public function testValidateCurrency3()
    {
        $this->assertEquals(
            $this->config->getCsvConfig()['currency_types'][0],
            $this->service->validateCurrency( $this->config->getCsvConfig()['currency_types'][0]));
    }
}

