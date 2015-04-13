<?php

namespace ITE\Common\LazyTableData\Worker\XLS;

use ITE\Common\LazyTableData\ApiWorkerInterface;
use ITE\Common\LazyTableData\Data\Row;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * Class XLSWorker
 *
 * @author sam0delkin <t.samodelkin@gmail.com>
 */
class XLSWorker implements ApiWorkerInterface
{
    /**
     * @var string
     */
    private $fileName;
    /**
     * @var int
     */
    private $workSheetId;

    /**
     * @var array
     */
    private $data;

    /**
     * @var PHPExcel
     */
    private $excelInstance;

    /**
     * @param string $fileName
     * @param  int   $workSheetId
     * @throws \Exception
     */
    function __construct($fileName, $workSheetId)
    {
        if (!class_exists('PHPExcel')) {
            throw new \Exception('You should install "phpoffice/phpexcel" composer package for use this worker.');
        }

        if (!file_exists($fileName)) {
            throw new \InvalidArgumentException(sprintf('File "%s" does not exists.', $fileName));
        }

        $this->fileName    = $fileName;
        $this->workSheetId = $workSheetId;
    }


    /**
     * @param int $rowNumber
     * @return array
     */
    public function loadRow($rowNumber)
    {
        $this->loadWholeTable();

        return $this->data[$rowNumber];
    }

    /**
     * @param int $rowNumber
     * @param int $cellNumber
     * @return string
     */
    public function loadCell($rowNumber, $cellNumber)
    {
        $this->loadWholeTable();

        return $this->data[$rowNumber][$cellNumber];
    }

    /**
     * @return Row[]
     */
    public function loadWholeTable()
    {
        if (!empty($this->data)) {
            return $this->data;
        }

        $excel   = $this->excelInstance = PHPExcel_IOFactory::load($this->fileName);
        $rowData = $excel->getSheet($this->workSheetId)->toArray();

        foreach ($rowData as $number => $row) {
            $this->data []= new Row($this, $number, $row);
        }

        return $this->data;
    }

    /**
     * @param Row[] $rowData
     * @return null
     */
    public function saveWholeTable($rowData)
    {
        $this->data = $rowData;
        $this->excelInstance->getSheet($this->workSheetId)->fromArray($this->data);
        $writer = PHPExcel_IOFactory::createWriter($this->excelInstance);
        $writer->save($this->fileName);
    }

    /**
     * @param int $rowNumber
     * @param Row $rowData
     * @return null
     */
    public function updateRow($rowNumber, Row $rowData)
    {
        $this->data[$rowNumber] = $rowData;
        $this->saveWholeTable($this->data);
    }

    /**
     * @param int    $rowNumber
     * @param int    $cellNumber
     * @param string $cellData
     * @return null
     */
    public function updateCell($rowNumber, $cellNumber, $cellData)
    {
        $this->data[$rowNumber][$cellNumber] = $cellData;
        $this->saveWholeTable($this->data);
    }

    /**
     * @param int $rowNumber
     * @return null
     */
    public function removeRow($rowNumber)
    {
        unset($this->data[$rowNumber]);
        $this->saveWholeTable($this->data);
    }

    /**
     * @param Row $rowData
     * @return null
     */
    public function insertRow(Row $rowData)
    {
        $this->data[]= $rowData;
        $this->saveWholeTable($this->data);
    }

    /**
     * @param int $rowNumber
     * @return bool
     */
    public function rowExists($rowNumber)
    {
        $this->loadWholeTable();

        return isset($this->data[$rowNumber]);
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        $this->loadWholeTable();

        return count($this->data);
    }

    /**
     * @return int
     */
    public function getSpreadsheetCount()
    {
        $this->loadWholeTable();

        return $this->excelInstance->getSheetCount();
    }

}