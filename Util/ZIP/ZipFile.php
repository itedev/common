<?php

namespace ITE\Common\Util\ZIP;

/**
 * Class ZipFile
 *
 */
class ZipFile
{
    /**
     * @var string The filename
     */
    private $name;

    /**
     * @var mixed The file content
     */
    private $content;

    /**
     * Public constructor.
     *
     * @param $name
     * @param $content
     */
    function __construct($name, $content)
    {
        $this->name    = $name;
        $this->content = $content;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Move file data to path.
     *
     * @param string $path
     * @param string $fileName
     */
    public function move($path, $fileName)
    {
        file_put_contents($path.'/'.$fileName, $this->content);
    }
}