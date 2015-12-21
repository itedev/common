<?php

namespace ITE\Common\LazyTableData;

use ITE\Common\LazyTableData\Data\Row;

/**
 * Interface ApiWorkerInterface
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
interface ApiWorkerInterface
{
    /**
     * @param int $rowNumber
     * @return array
     */
    public function loadRow($rowNumber);

    /**
     * @param int $rowNumber
     * @param int $cellNumber
     * @return string
     */
    public function loadCell($rowNumber, $cellNumber);

    /**
     * @return Row[]
     */
    public function loadWholeTable();

    /**
     * @param Row[] $rowData
     * @param string|null $fileName
     * @return null
     */
    public function saveWholeTable($rowData, $fileName = null);

    /**
     * @param int $rowNumber
     * @param Row $rowData
     * @return null
     */
    public function updateRow($rowNumber, Row $rowData);

    /**
     * @param int $rowNumber
     * @param int $cellNumber
     * @param string $cellData
     * @return null
     */
    public function updateCell($rowNumber, $cellNumber, $cellData);

    /**
     * @param int $rowNumber
     * @return null
     */
    public function removeRow($rowNumber);

    /**
     * @param Row $rowData
     * @return null
     */
    public function insertRow(Row $rowData);

    /**
     * @param int $rowNumber
     * @return bool
     */
    public function rowExists($rowNumber);

    /**
     * @return int
     */
    public function getRowsCount();

    /**
     * @param $worksheetId
     */
    public function setWorksheet($worksheetId);

    /**
     * @return mixed
     */
    public function getWorksheet();

    /**
     * @return mixed
     */
    public function getFirstRowIndex();
}
