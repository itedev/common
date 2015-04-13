<?php

namespace ITE\Common\LazyTableData\Worker\GoogleSpreadsheet;


use ZendGData\Spreadsheets\CellEntry;
use ZendGData\Spreadsheets\CellFeed;

/**
 * Class BatchRequest
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class BatchRequest
{
    /**
     *
     * @var CellEntry[]
     */
    protected $entries;

    /**
     *
     */
    public function __construct()
    {
        $this->entries = array();
    }

    /**
     *
     * @param CellEntry $cellEntry
     */
    public function addEntry(CellEntry $cellEntry)
    {
        $this->entries[] = $cellEntry;
    }

    /**
     *
     * @param CellFeed $cellFeed
     * @return null|string
     */
    public function createRequestXml(CellFeed $cellFeed)
    {
        if (count($this->entries) === 0) {
            return null;
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
            <feed xmlns="http://www.w3.org/2005/Atom"
            xmlns:batch="http://schemas.google.com/gdata/batch"
            xmlns:gs="http://schemas.google.com/spreadsheets/2006">';

        $xml .= '<id>' . $cellFeed->getLink('http://schemas.google.com/g/2005#batch')->getHref() . '</id>';

        $i = 1;
        foreach ($this->entries as $cellEntry) {
            $xml .= $this->createEntry($cellEntry, $i++, $cellFeed);
        }

        $xml .= '</feed>';

        return $xml;
    }

    /**
     *
     * @param CellEntry $cellEntry
     * @param string                                  $index
     * @param CellFeed   $cellFeed
     * @return string
     */
    protected function createEntry(CellEntry $cellEntry, $index, CellFeed $cellFeed)
    {
        $id = explode('/', $cellEntry->getId()->getText());
        $id = $id[(count($id) - 1)];
        return sprintf(
          '<entry>
                <batch:id>%s</batch:id>
                <batch:operation type="update"/>
                <id>%s</id>
                <link rel="edit" type="application/atom+xml"
                  href="%s"/>
                <gs:cell row="%s" col="%s" inputValue="%s"/>
            </entry>',
          'A' . $index,
          $cellFeed->getLink('http://schemas.google.com/g/2005#post')->getHref() . "/" . $id,
          $cellEntry->getLink('edit')->getHref(),
          $cellEntry->getCell()->getRow(),
          $cellEntry->getCell()->getColumn(),
          str_replace('&', '&amp;', $cellEntry->getCell()->getInputValue())
        );
    }
}