<?php

namespace ITE\Common\LazyTableData\Worker\GoogleSpreadsheet;

use ITE\Common\LazyTableData\LazyTableData;

/**
 * Class GoogleSpreadsheetManager
 *
 * @author sam0delkin <t.samodelkin@gmail.com>
 */
class GoogleSpreadsheetManager
{
    /**
     * Returns LazyTable wrapper for given Google account and spreadsheet info.
     *
     * @param string $userName
     * @param string $password
     * @param string $documentId
     * @param null   $worksheetTitle
     * @return LazyTableData
     */
    public static function getTable($userName, $password, $documentId, $worksheetTitle = null)
    {
        $worksheetId = GoogleApiWorker::getWorksheetId($userName, $password, $documentId, $worksheetTitle);
        if (!$worksheetId) {
            throw new \InvalidArgumentException(sprintf('Worksheet with name "%s" is not existing.', $worksheetTitle));
        }
        $apiWorker = new GoogleApiWorker($userName, $password, $documentId, $worksheetId);
        $lazyTable = new LazyTableData($apiWorker);

        return $lazyTable;
    }
}