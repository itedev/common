<?php

namespace ITE\Common\Util\ZIP;


/**
 * Class ZipIterator
 *
 * @package EXP\PricingEngineBundle\Core\Manager\Util
 */
class ZipIterator implements \Iterator, \Countable
{

    /**
     * @var \ZipArchive
     */
    private $zip;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * Public constructor
     *
     * @param $fileName string The ZIP filename to iterate.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($fileName)
    {
        $this->zip = new \ZipArchive();
        if (!($errorCode = $this->zip->open($fileName))) {
            throw new \InvalidArgumentException('Unable to open zip archive: '.$errorCode);
        }
        if ($this->zip->numFiles === 0) {
            throw new \InvalidArgumentException('Archive is broken or no files in archive.');
        }
    }


    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $name    = $this->zip->getNameIndex($this->index);
        $content = $this->zip->getFromIndex($this->index);

        return new ZipFile($name, $content);
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->index++;
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->index;
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
        $name = $this->zip->getNameIndex($this->index);

        return $name !== false;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->index = 0;
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
        return $this->zip->numFiles;
    }
} 