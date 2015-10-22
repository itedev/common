<?php

namespace ITE\Common\CdnJs;

/**
 * Class CdnAssetReference
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class CdnAssetReference
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $name
     * @param string $version
     * @param string $filePath
     */
    public function __construct($name, $version, $filePath)
    {
        $this->name = $name;
        $this->version = $version;
        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('//cdnjs.cloudflare.com/ajax/libs/%s/%s/%s', $this->name, $this->version, $this->filePath);
    }
}
