<?php

namespace ITE\Common\LazyTableData;

use ITE\Common\LazyTableData\Data\Row;

/**
 * Class LazyTableData
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class LazyTableData implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * Will load cell only if it's needed.
     */
    const FETCH_TYPE_LAZY = 2;

    /**
     * Will load row only if it's needed.
     */
    const FETCH_TYPE_DEFAULT = 1;

    /**
     * Will load all spreadsheet at first call
     */
    const FETCH_TYPE_EAGER = 0;

    /**
     *  Will save data by row
     */
    const SAVE_TYPE_LAZY = 2;

    /**
     * Will save data by cell (default)
     */
    const SAVE_TYPE_EAGER = 1;

    /**
     * @var Row[]
     */
    private $dataCache = [];

    /**
     * @var ApiWorkerInterface
     */
    private $worker;
    /**
     * @var int
     */
    private $fetchType = self::FETCH_TYPE_DEFAULT;

    /**
     * @var int
     */
    private $saveType = self::SAVE_TYPE_EAGER;

    /**
     * @var bool
     */
    private $fullyLoaded = false;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @param ApiWorkerInterface $worker
     */
    function __construct(ApiWorkerInterface $worker)
    {
        $this->worker = $worker;
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

        return $this->worker->rowExists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @return Row Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (isset($this->dataCache[$offset])) {
            return $this->dataCache[$offset];
        }

        $originalExists = $this->offsetExists($offset);

        $this->dataCache[$offset] = new Row($this->worker, $offset);
        $this->dataCache[$offset]->setSaveType($this->saveType);

        if ($this->fetchType === self::FETCH_TYPE_DEFAULT) {
            if($originalExists) {
                $this->dataCache[$offset]->toArray();
            }
        }

        return $this->dataCache[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed     $offset   <p>
     *                            The offset to assign the value to.
     *                            </p>
     * @param Row|array $value    <p>
     *                            The value to set.
     *                            </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof Row) {
            $this->dataCache[$offset] = $value;
        } elseif (is_array($value)) {
            $this->dataCache[$offset] = new Row($this->worker, $offset, $value);
        } else {
            throw new \InvalidArgumentException(
              sprintf(
                'You can use only "array" or "%s" parameter types. "%s" given.',
                get_class(new Row($this->worker, 1)),
                gettype($value)
              )
            );
        }

        $this->worker->updateRow($offset, $this->dataCache[$offset]);
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
        $this->worker->removeRow($offset);
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
        return $this->worker->getRowsCount();
    }

    /**
     * @param int $fetchType
     */
    public function setFetchType($fetchType)
    {
        $this->fetchType = $fetchType;
        if($fetchType === self::FETCH_TYPE_EAGER){
            $this->loadAllData();
        }
    }

    /**
     *
     */
    private function loadAllData()
    {
        if ($this->fullyLoaded) {
            return;
        }
        $this->dataCache = $this->worker->loadWholeTable();
        foreach ($this->dataCache as $row) {
            $row->setSaveType($this->saveType);
        }

        $this->fullyLoaded = true;
    }

    /**
     * @param int $saveType
     */
    public function setSaveType($saveType)
    {
        $this->saveType = $saveType;
    }

    public function save()
    {
        $this->worker->saveWholeTable($this->dataCache);
    }

    /**
     * @return Data\Row[]
     */
    public function toArray()
    {
        $this->loadAllData();

        return $this->dataCache;
    }

    /**
     * @return ApiWorkerInterface
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->offsetGet($this->position);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->offsetExists($this->position);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = $this->worker->getFirstRowIndex();
    }

    /**
     * @param $worksheetId
     */
    public function setWorksheet($worksheetId)
    {
        $this->worker->setWorksheet($worksheetId);
        $this->dataCache = [];
    }

    /**
     * @return mixed
     */
    public function getWorksheet()
    {
        return $this->worker->getWorksheet();
    }

}