<?php

namespace ITE\Common\Util\CSV;

/**
 * Class CSVIterator
 *
 */
class CSVIterator implements \Iterator, \ArrayAccess, \Countable
{

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $index = -1;

    /**
     * @var bool
     */
    private $isOpen = false;

    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var string
     */
    private $fileName;

    /**
     * Opens CSV file to iterate
     *
     * @param        $filename
     * @param string $delimiter
     * @throws \InvalidArgumentException
     */
    public function open($filename, $delimiter = ';')
    {

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File "'.$filename.'" is not found.');
        }
        $csvFile = fopen($filename, "r");

        if (!$csvFile) {
            throw new \InvalidArgumentException('No CSV data found in file "'.$filename.'" or permission denied.');
        }
        while (!feof($csvFile)) {
            $this->data [] = fgetcsv($csvFile, null, $delimiter);
        }

        $this->isOpen = true;
        $this->index  = 0;
        $this->delimiter = $delimiter;
        $this->fileName = $filename;
    }


    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @throws \Exception
     * @return mixed Can return any type.
     */
    public function current()
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }

        return current($this->data);
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @throws \Exception
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }
        $this->index++;
        next($this->data);
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @throws \Exception
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }

        return key($this->data);
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->data[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @throws \Exception
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }
        $this->index = 0;
        reset($this->data);
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     * @throws \Exception
     * @return boolean true on success or false on failure.
     *                      </p>
     *                      <p>
     *                      The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }

        return isset($this->data[$this->index]);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     * @throws \Exception
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }

        return $this->data[$offset];
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     * @throws \Exception
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }

        $this->data[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     * @throws \Exception
     * @return void
     */
    public function offsetUnset($offset)
    {
        if (!$this->isOpen) {
            throw new \Exception("No CSV file opened");
        }

        unset($this->data[$offset]);
    }

    /**
     * Returns all data as array
     *
     * @return array
     */
    public function all()
    {
        return $this->data;
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
        return count($this->data);
    }

    /**
     * @param string|null $fileName
     */
    public function save($fileName = null)
    {
        $csvData = '';
        foreach ($this->data as $row) {
            $csvData .= implode($this->delimiter, $row) . "\n";
        }
        $csvData = substr($csvData, 0, strlen($csvData) - 1);

        file_put_contents($fileName ?: $this->fileName, $csvData);
    }
}