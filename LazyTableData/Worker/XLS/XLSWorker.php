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
        $this->excelInstance = PHPExcel_IOFactory::load($this->fileName);
    }

    /**
     * @param $worksheetId
     */
    public function setWorksheet($worksheetId)
    {
        $this->workSheetId = $worksheetId;
    }


    /**
     * @param int $rowNumber
     * @return array
     */
    public function loadRow($rowNumber)
    {
        $row      = [];
        $iterator = $this->excelInstance->getSheet($this->workSheetId)
            ->getRowIterator($rowNumber)
            ->current()
            ->getCellIterator();

        foreach ($iterator as $cell) {
            $row[] = $cell->getValue();
        }



        return $row;
    }

    /**
     * @param int $rowNumber
     * @param int $cellNumber
     * @return string
     */
    public function loadCell($rowNumber, $cellNumber)
    {
        return $this->excelInstance->getSheet($this->workSheetId)
            ->getCellByColumnAndRow($cellNumber, $rowNumber)
            ->getValue();
    }

    /**
     * @return Row[]
     */
    public function loadWholeTable()
    {
        $rowData = $this->excelInstance->getSheet($this->workSheetId)->toArray();
        $data = [];

        foreach ($rowData as $number => $row) {
            $data []= new Row($this, $number, $row);
        }

        return $data;
    }

    /**
     * @param Row[] $rowData
     * @return null
     */
    public function saveWholeTable($rowData)
    {
        $this->excelInstance->getSheet($this->workSheetId)->fromArray($rowData);
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
        $iterator = $this->excelInstance->getSheet($this->workSheetId)->getRowIterator($rowNumber)->current()->getCellIterator();

        foreach ($iterator as $key => $cell) {
            $cell->setValue($rowData[$key]);
        }

        $writer = PHPExcel_IOFactory::createWriter($this->excelInstance);
        $writer->save($this->fileName);
    }

    /**
     * @param int    $rowNumber
     * @param int    $cellNumber
     * @param string $cellData
     * @return null
     */
    public function updateCell($rowNumber, $cellNumber, $cellData)
    {
        $iterator = $this->excelInstance->getSheet($this->workSheetId)->getRowIterator($rowNumber)->current()->getCellIterator();

        foreach ($iterator as $key => $cell) {
            if ($key == $cellNumber) {
               $cell->setValue($cellData);
            }
        }

        $writer = PHPExcel_IOFactory::createWriter($this->excelInstance);
        $writer->save($this->fileName);
    }

    /**
     * @param int $rowNumber
     * @return null
     */
    public function removeRow($rowNumber)
    {
        $this->excelInstance->getSheet($this->workSheetId)->removeRow($rowNumber);
        $writer = PHPExcel_IOFactory::createWriter($this->excelInstance);
        $writer->save($this->fileName);
    }

    /**
     * @param Row $rowData
     * @return null
     */
    public function insertRow(Row $rowData)
    {
        $sheet = $this->excelInstance->getSheet($this->workSheetId);
        $sheet->insertNewRowBefore();
        $this->updateRow(1, $rowData);
    }

    /**
     * @param int $rowNumber
     * @return bool
     */
    public function rowExists($rowNumber)
    {
        return $rowNumber < $this->getRowsCount();
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        return $this->excelInstance->getSheet($this->workSheetId)->getHighestRow();
    }

    /**
     * @return int
     */
    public function getSpreadsheetCount()
    {
       return $this->excelInstance->getSheetCount();
    }

    /**
     * @return mixed
     */
    public function getFirstRowIndex()
    {
        return 1;
    }

    /**
     * @return mixed
     */
    public function getWorksheet()
    {
        return $this->workSheetId;
    }


}