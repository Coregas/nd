<?php
namespace Paysera\Services;

use League\Csv\Reader;
use League\Csv\Statement;
use AppConfig\Config;
use Paysera\Services\CsvValidator;

class FileManager
{
    /**
     * @var array
     */
    private $csvConfig;
    /**
     * @var CsvValidator
     */
    private $csvValidator;

    public function __construct(
        Config $config,
        CsvValidator $csvValidator
    ){
        $this->csvConfig = $config->getCsvConfig();
        $this->csvValidator = $csvValidator;
    }

    /**
     * @param string $fileName
     * @return mixed
     */
    public function readFile(string $fileName)
   {
       $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

       try {
            switch ($fileExtension){
                case 'csv':
                    return $this->readCSVFile($fileName);
                    break;
                default:
                    throw new \Exception('unsupported file type');
            }

       } catch (\Exception $e) {
           fwrite(STDOUT, $e->getMessage());
           die();
       }
   }

    /**
     * @param string $fileName
     * @return mixed
     */
   private function readCSVFile(string $fileName)
   {
       /**
        * For Macintosh computer compatability
        */
       if (!ini_get("auto_detect_line_endings")) {
           ini_set("auto_detect_line_endings", '1');
       }

       $csv = Reader::createFromPath($fileName, 'r');
       $data = $csv->getRecords();

       $sortedData = [];
       foreach ($data as $key => $row) {
           $sortedData[$key] = $this->validateCsvRowData($row);
           $sortedData[$key]['id'] = $key;
       }

       return $sortedData;
   }

    /**
     * @param array $rowData
     * @return array
     */
   private function validateCsvRowData(array $rowData)
   {
       $this->csvValidator->validateColumnCount($rowData);
       $sortedRowData = [];

       foreach($rowData as $key => $column) {
           switch ($this->csvConfig['csv_file_headers'][$key]) {
               case 'date':
                   $sortedRowData['date'] = $this->csvValidator->validateDate($column);
                   break;
               case 'user_id':
                   $sortedRowData['user_id'] = $this->csvValidator->validateUserId($column);
                   break;
               case 'user_type':
                   $sortedRowData['user_type'] = $this->csvValidator->validateUserType($column);
                   break;
               case 'operation':
                   $sortedRowData['transaction_type'] = $this->csvValidator->validateTransactionType($column);
                   break;
               case 'amount':
                   $sortedRowData['amount'] = $this->csvValidator->validateAmount($column);
                   break;
               case 'currency':
                   $sortedRowData['currency'] = $this->csvValidator->validateCurrency($column);
                   break;
           }
       }
       return $sortedRowData;
   }
}