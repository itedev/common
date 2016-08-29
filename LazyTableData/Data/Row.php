<?php

namespace ITE\Common\LazyTableData\Data;

use ITE\Common\LazyTableData\ApiWorkerInterface;
use ITE\Common\LazyTableData\LazyTableData;

/**
 * Class Row
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class Row implements \ArrayAccess, \Countable
{
    /**
     * @var []
     */
    private $dataCache = [];

    /**
     * @var ApiWorkerInterface
     */
    private $worker;

    /**
     * @var int
     */
    private $rowNumber;

    /**
     * @var bool
     */
    private $isFullyLoaded = false;

    /**
     * @var int
     */
    private $saveType = LazyTableData::SAVE_TYPE_EAGER;

    /**
     * @param ApiWorkerInterface $worker
     * @param int                $rowNumber
     * @param array              $rowData
     */
    public function __construct(ApiWorkerInterface $worker, $rowNumber, $rowData = [])
    {
        $this->worker    = $worker;
        $this->rowNumber = $rowNumber;
        $this->dataCache = $rowData;
        if (count($rowData)) {
            $this->isFullyLoaded = true;
        }
    }

    /**
     * @param int $saveType
     */
    public function setSaveType($saveType)
    {
        $this->saveType = $saveType;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @return boolean true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        if (isset($this->dataCache[$offset])) {
            return true;
        }

        if ($this->isFullyLoaded) {
            return false;
        }

        $cellValue = $this->worker->loadCell($this->rowNumber, $offset);
        if ($cellValue) {
            $this->dataCache[$offset] = $cellValue;

            return true;
        }

        return false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (isset($this->dataCache[$offset])) {
            return $this->dataCache[$offset];
        }

        return $this->dataCache[$offset] = $this->worker->loadCell($this->rowNumber, $offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (isset($this->dataCache[$offset]) && $this->dataCache[$offset] === $value) {
            return;
        }

        $this->dataCache[$offset] = $value;
        if ($this->saveType === LazyTableData::SAVE_TYPE_EAGER) {
            $this->worker->updateCell($this->rowNumber, $offset, $value);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->dataCache[$offset]);
        if ($this->saveType === LazyTableData::SAVE_TYPE_EAGER) {
            $this->worker->updateCell($this->rowNumber, $offset, '');
        }
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        if ($this->isFullyLoaded) {
            return count($this->dataCache);
        }
        $this->dataCache     = $this->worker->loadRow($this->rowNumber);
        $this->isFullyLoaded = true;

        return count($this->dataCache);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (!$this->isFullyLoaded) {
            $this->dataCache     = $this->worker->loadRow($this->rowNumber);
            $this->isFullyLoaded = true;
        }

        return $this->dataCache;
    }

    /**
     * Saves data to worker.
     */
    public function save()
    {
        $this->worker->updateRow($this->rowNumber, $this);
    }

    /**
     * @return $this
     */
    public function clearCache()
    {
        $this->dataCache = [];

        return $this;
    }
}
