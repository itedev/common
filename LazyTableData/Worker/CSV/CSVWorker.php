<?php

namespace ITE\Common\LazyTableData\Worker\CSV;

use ITE\Common\LazyTableData\ApiWorkerInterface;
use ITE\Common\LazyTableData\Data\Row;
use ITE\Common\Util\CSV\CSVIterator;

/**
 * Class CSVWorker
 *
 * @author sam0delkin <t.samodelkin@gmail.com>
 */
class CSVWorker implements ApiWorkerInterface
{
    /**
     * @var CSVIterator
     */
    private $csvIterator;

    /**
     * @param        $fileName
     * @param string $delimiter
     */
    public function __construct($fileName, $delimiter = ';')
    {
        $this->csvIterator = new CSVIterator();
        $this->csvIterator->open($fileName, $delimiter);
    }

    /**
     * @param int $rowNumber
     * @return array
     */
    public function loadRow($rowNumber)
    {
        return $this->csvIterator[$rowNumber];
    }

    /**
     * @param int $rowNumber
     * @param int $cellNumber
     * @return string
     */
    public function loadCell($rowNumber, $cellNumber)
    {
        return $this->csvIterator[$rowNumber][$cellNumber];
    }

    /**
     * @return Row[]
     */
    public function loadWholeTable()
    {
        return $this->csvIterator;
    }

    /**
     * {@inheritdoc}
     */
    public function saveWholeTable($rowData, $fileName = null)
    {
        foreach ($rowData as $key => $data) {
            $this->csvIterator[$key] = $data->toArray();
        }

        $this->csvIterator->save($fileName);
    }

    /**
     * @param int $rowNumber
     * @param Row $rowData
     * @return null
     */
    public function updateRow($rowNumber, Row $rowData)
    {
        $this->csvIterator[$rowNumber] = $rowData->toArray();
        $this->csvIterator->save();
    }

    /**
     * @param int    $rowNumber
     * @param int    $cellNumber
     * @param string $cellData
     * @return null
     */
    public function updateCell($rowNumber, $cellNumber, $cellData)
    {
        $this->csvIterator[$rowNumber][$cellNumber] = $cellData;
        $this->csvIterator->save();
    }

    /**
     * @param int $rowNumber
     * @return null
     */
    public function removeRow($rowNumber)
    {
        unset($this->csvIterator[$rowNumber]);
        $this->csvIterator->save();
    }

    /**
     * @param Row $rowData
     * @return null
     */
    public function insertRow(Row $rowData)
    {
        $this->csvIterator []= $rowData->toArray();
        $this->csvIterator->save();
    }

    /**
     * @param int $rowNumber
     * @return bool
     */
    public function rowExists($rowNumber)
    {
        return isset($this->csvIterator[$rowNumber]);
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        return $this->csvIterator->count();
    }

    /**
     * @param $worksheetId
     */
    public function setWorksheet($worksheetId)
    {

    }

    /**
     * @return mixed
     */
    public function getWorksheet()
    {
        return null;
    }

    /**
     * @return mixed
     */
    public function getFirstRowIndex()
    {
        return 0;
    }
}
