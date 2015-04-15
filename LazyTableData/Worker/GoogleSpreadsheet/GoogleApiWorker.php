<?php

namespace ITE\Common\LazyTableData\Worker\GoogleSpreadsheet;

use ITE\Common\LazyTableData\ApiWorkerInterface;
use ITE\Common\LazyTableData\Data\Row;
use Zend\Http\Client\Adapter\Curl;
use ZendGData\ClientLogin;
use ZendGData\HttpClient;
use ZendGData\Spreadsheets;
use ZendGData\Spreadsheets\CellEntry;
use ZendGData\Spreadsheets\CellFeed;
use ZendGData\Spreadsheets\CellQuery;
use ZendGData\Spreadsheets\Extension\RowCount;
use ZendGData\Spreadsheets\ListEntry;
use ZendGData\Spreadsheets\ListFeed;
use ZendGData\Spreadsheets\WorksheetEntry;
use ZendGData\Spreadsheets\WorksheetFeed;

/**
 * Class GoogleApiWorker
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class GoogleApiWorker implements ApiWorkerInterface
{
    /**
     * @var string
     */
    protected $userName;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $spreadsheetId;

    /**
     * @var string
     */
    protected $worksheetId;

    /**
     * @var Spreadsheets
     */
    protected $service;

    /**
     * @param $userName
     * @param $password
     * @param $spreadsheetId
     * @param $worksheetId
     *
     * @throws \Exception
     */
    function __construct($userName, $password, $spreadsheetId, $worksheetId)
    {
        if (!class_exists('ZendGData\HttpClient')) {
            throw new \Exception('You should install "zendframework/zendgdata" composer package for use this worker.');
        }

        $this->userName      = $userName;
        $this->password      = $password;
        $this->spreadsheetId = $spreadsheetId;
        $this->worksheetId   = $worksheetId;
    }

    /**
     * @param $userName
     * @param $password
     * @param $spreadsheetId
     * @param $worksheetTitle
     * @return null|\ZendGData\App\Extension\Id
     *
     * @throws \Exception
     */
    public static function getWorksheetId($userName, $password, $spreadsheetId, $worksheetTitle)
    {
        if (!class_exists('ZendGData\HttpClient')) {
            throw new \Exception('You should install "zendframework/zendgdata" composer package gor use this worker.');
        }

        $service    = Spreadsheets::AUTH_SERVICE_NAME;
        $httpClient = new HttpClient();
        $curl       = new Curl();
        $curl->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setCurlOption(CURLOPT_TIMEOUT, 60);
        $httpClient->setAdapter($curl);
        $client  = ClientLogin::getHttpClient(
          $userName,
          $password,
          $service,
          $httpClient
        );
        $service = new Spreadsheets($client);

        $query = new Spreadsheets\DocumentQuery();
        $query->setSpreadsheetKey($spreadsheetId);
        if ($worksheetTitle) {
            $query->setTitle($worksheetTitle);
        }
        /** @var WorksheetFeed $feed */
        $feed = $service->getWorksheetFeed($query);
        if ($feed->count()) {
            /** @var WorksheetEntry $worksheet */
            $worksheet = $feed[0];

            $worksheetUrl = $worksheet->getId()->getText();
            $parts        = explode('/', $worksheetUrl);

            return $parts[count($parts) - 1];
        }

        return null;
    }

    protected function initializeIfNeeded()
    {
        if (!$this->service) {
            $service    = Spreadsheets::AUTH_SERVICE_NAME;
            $httpClient = new HttpClient();
            $curl       = new Curl();
            $curl->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
            $curl->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
            $curl->setCurlOption(CURLOPT_TIMEOUT, 60);
            $httpClient->setAdapter($curl);
            $client        = ClientLogin::getHttpClient(
              $this->userName,
              $this->password,
              $service,
              $httpClient
            );
            $this->service = new Spreadsheets($client);
        }
    }

    /**
     * @return CellQuery
     */
    protected function getEmptyCellQuery()
    {
        $query = new CellQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);
        $query->setWorksheetId($this->worksheetId);

        return $query;
    }


    /**
     * @param int $rowNumber
     * @return array
     */
    public function loadRow($rowNumber)
    {
        $this->initializeIfNeeded();

        $query = $this->getEmptyCellQuery();
        $query->setMinRow($rowNumber);
        $query->setMaxRow($rowNumber);

        /** @var CellFeed $cellFeed */
        try {
            $cellFeed = $this->service->getCellFeed($query);
        } catch (\Exception $e) {
            return null;
        }
        $data = [];

        /** @var CellEntry $cellEntry */
        foreach ($cellFeed as $key => $cellEntry) {
            $data[$cellEntry->getCell()->getColumn()] = $cellEntry->getCell()->getInputValue();
        }

        return $data;
    }

    /**
     * @param int $rowNumber
     * @param int $cellNumber
     * @return string
     */
    public function loadCell($rowNumber, $cellNumber)
    {
        $this->initializeIfNeeded();

        $query = $this->getEmptyCellQuery();
        $query->setMinRow($rowNumber);
        $query->setMaxRow($rowNumber);
        $query->setMinCol($cellNumber);
        $query->setMaxCol($cellNumber);

        /** @var CellFeed $cellFeed */
        $cellFeed = $this->service->getCellFeed($query);

        if (!$cellFeed[0]) {
            return null;
        }

        return $cellFeed[0]->getCell()->getInputValue();
    }

    /**
     * @param int $rowNumber
     * @param Row $rowData
     * @return null
     *
     * @throws \Exception
     */
    public function updateRow($rowNumber, Row $rowData)
    {
        $this->initializeIfNeeded();


        $query = $this->getEmptyCellQuery();
        $query->setMinRow($rowNumber);
        $query->setMaxRow($rowNumber);
        $query->setReturnEmpty(true);

        /** @var CellFeed $cellFeed */
        try {
            $cellFeed = $this->service->getCellFeed($query);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Invalid query parameter value for min-row.') !== false) {
                $this->incrementRowsCount();

                return $this->updateRow($rowNumber, $rowData);
            }

            throw $e;
        }
        if (!$cellFeed[0]) {
            $this->incrementRowsCount();
        }
        $batchRequest = new BatchRequest();

        /** @var CellEntry $cellEntry */
        foreach ($cellFeed as $key => $cellEntry) {
            $cellEntry->getCell()->setInputValue($rowData[$cellEntry->getCell()->getColumn()]);
            $batchRequest->addEntry($cellEntry);
        }

        $xml = $batchRequest->createRequestXml($cellFeed);

        $resp = $this->service->post($xml, $cellFeed->getLink('http://schemas.google.com/g/2005#batch')->getHref());

    }

    /**
     * @param int    $rowNumber
     * @param int    $cellNumber
     * @param string $cellData
     * @return null
     */
    public function updateCell($rowNumber, $cellNumber, $cellData)
    {
        $this->initializeIfNeeded();

        /** @var CellFeed $cellFeed */
        try {
            $this->service->updateCell($rowNumber, $cellNumber, $cellData, $this->spreadsheetId, $this->worksheetId);
        } catch (\Exception $e) {
            if($this->getRowsCount() < $rowNumber) {
                $this->incrementRowsCount();
            }
            $this->service->updateCell($rowNumber, $cellNumber, $cellData, $this->spreadsheetId, $this->worksheetId);

            return;
        }
    }

    /**
     * @param int $rowNumber
     * @return null
     */
    public function removeRow($rowNumber)
    {
        $this->initializeIfNeeded();

        $query = $this->getEmptyCellQuery();
        $query->setMinRow($rowNumber);
        $query->setMaxRow($rowNumber);
        /** @var ListFeed $listFeed */
        $listFeed = $this->service->getListFeed($query);
        /** @var ListEntry $listEntry */
        $listEntry = $listFeed->offsetGet(0);

        if (!$listEntry) {
            return;
        }

        $listEntry->delete();
    }

    /**
     * @param Row $rowData
     * @return ListEntry
     */
    public function insertRow(Row $rowData)
    {
        $newEntry = new Spreadsheets\ListEntry();
        foreach ($rowData->toArray() as $k => $v) {
            $newCustom = new Spreadsheets\Extension\Custom();
            $newCustom->setText($v)->setColumnName($k);
            $newEntry->addCustom($newCustom);
        }

        $query = new Spreadsheets\ListQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);
        $query->setWorksheetId($this->worksheetId);
        $query->setMaxResults(1);

        $feed     = $this->service->getListFeed($query);
        $editLink = $feed->getLink('http://schemas.google.com/g/2005#post');

        return $this->service->insertEntry($newEntry->saveXML(), $editLink->href, 'ZendGData\Spreadsheets\ListEntry');
    }

    /**
     * @param int $rowNumber
     * @return bool
     */
    public function rowExists($rowNumber)
    {
        $this->initializeIfNeeded();

        $query = $this->getEmptyCellQuery();
        $query->setMinRow($rowNumber);
        $query->setMaxRow($rowNumber);
        /** @var ListFeed $listFeed */
        try {
            $listFeed = $this->service->getListFeed($query);
        } catch (\Exception $e) {
            return false;
        }
        /** @var ListEntry $listEntry */
        $listEntry = $listFeed->offsetGet(0);

        if (!$listEntry) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getRowsCount()
    {
        $this->initializeIfNeeded();

        $query = new Spreadsheets\DocumentQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);

        /** @var WorksheetFeed $feed */
        $feed = $this->service->getWorksheetFeed($query);
        /** @var WorksheetEntry $worksheet */
        foreach ($feed as $worksheet) {
            if (strpos($worksheet->getId()->getText(), $this->worksheetId) !== false) {
                /** @var RowCount $rowCount */
                $rowCount = $worksheet->getRowCount();

                return $rowCount->getText();
            }
        }

        return null;
    }

    public function incrementRowsCount()
    {
        $this->initializeIfNeeded();

        $query = new Spreadsheets\DocumentQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);

        /** @var WorksheetFeed $feed */
        $feed = $this->service->getWorksheetFeed($query);
        /** @var WorksheetEntry $worksheet */
        foreach ($feed as $worksheet) {
            if (strpos($worksheet->getId()->getText(), $this->worksheetId) !== false) {
                /** @var RowCount $rowCount */
                $rowCount = $worksheet->getRowCount();

                $rowCount->setText((int) $rowCount->getText() + 1);
                $worksheet->setRowCount($rowCount);

                $this->service->updateEntry($worksheet);
            }
        }
    }

    /**
     * @param $count
     * @throws \ZendGData\App\Exception
     */
    public function setRowCount($count)
    {
        $this->initializeIfNeeded();

        $query = new Spreadsheets\DocumentQuery();
        $query->setSpreadsheetKey($this->spreadsheetId);

        /** @var WorksheetFeed $feed */
        $feed = $this->service->getWorksheetFeed($query);
        /** @var WorksheetEntry $worksheet */
        foreach ($feed as $worksheet) {
            if (strpos($worksheet->getId()->getText(), $this->worksheetId) !== false) {
                /** @var RowCount $rowCount */
                $rowCount = $worksheet->getRowCount();

                $rowCount->setText($count);
                $worksheet->setRowCount($rowCount);

                $this->service->updateEntry($worksheet);
            }
        }
    }

    /**
     * @return Row[]
     */
    public function loadWholeTable()
    {
        $this->initializeIfNeeded();

        $ret = [];
        $query = $this->getEmptyCellQuery();

        /** @var ListFeed $listFeed */
        try {
            $cellFeed = $this->service->getCellFeed($query);
        } catch (\Exception $e) {
            return false;
        }
        $currentRow = 1;
        $currentRowData = [];
        /** @var CellEntry $cellEntry */
        foreach ($cellFeed as $cellEntry) {
            if($cellEntry->getCell()->getRow() != $currentRow){
                $ret[$currentRow] = new Row($this, $currentRow, $currentRowData);
                $currentRow = $cellEntry->getCell()->getRow();
                $currentRowData = [];
            }
            $currentRowData[$cellEntry->getCell()->getColumn()] = $cellEntry->getCell()->getInputValue();
        }


        return $ret;
    }

    /**
     * @param Row[] $rowData
     * @return null
     */
    public function saveWholeTable($rowData)
    {
        $this->initializeIfNeeded();

        if (count($rowData) > $this->getRowsCount()) {
            $this->setRowCount(count($rowData));
        }

        $query = $this->getEmptyCellQuery();
        $query->setMinRow(1);
        $query->setMaxRow(count($rowData));
        $query->setReturnEmpty(true);

        /** @var CellFeed $cellFeed */
        $cellFeed = $this->service->getCellFeed($query);
        $batchRequest = new BatchRequest();

        /** @var CellEntry $cellEntry */
        foreach ($cellFeed as $key => $cellEntry) {
            if(isset($rowData[$cellEntry->getCell()->getRow()]) && isset($rowData[$cellEntry->getCell()->getRow()][$cellEntry->getCell()->getColumn()])) {
                $cellEntry->getCell()->setInputValue($rowData[$cellEntry->getCell()->getRow()][$cellEntry->getCell()->getColumn()]);
                $batchRequest->addEntry($cellEntry);
            }
        }

        $xml = $batchRequest->createRequestXml($cellFeed);

        $resp = $this->service->post($xml, $cellFeed->getLink('http://schemas.google.com/g/2005#batch')->getHref());
    }

    /**
     * @param $worksheetId
     */
    public function setWorksheet($worksheetId)
    {
        $this->worksheetId = $worksheetId;
    }

}