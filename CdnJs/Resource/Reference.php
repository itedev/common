<?php

namespace ITE\Common\CdnJs\Resource;

use ITE\Common\CdnJs\ApiWrapper;

/**
 * Class Reference
 *
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class Reference
{
    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @param        $packageName
     * @param        $version
     * @param        $fileName
     * @param string $protocol
     */
    public function __construct($packageName, $version, $fileName, $protocol = 'http')
    {
        $this->packageName = $packageName;
        $this->version = $version;
        $this->fileName = $fileName;
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return ApiWrapper::buildCdnUrl($this->packageName, $this->version, $this->fileName, $this->protocol);
    }
}