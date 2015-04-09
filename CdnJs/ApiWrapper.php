<?php

namespace ITE\Common\CdnJs;

/**
 * Class ApiWrapper
 *
 * @package ITE\Common\CdnJs
 * @author  sam0delkin <t.samodelkin@gmail.com>
 */
class ApiWrapper
{
    const API_URL = 'http://api.cdnjs.com/libraries';
    const CDN_HTTP_URL = 'http://cdnjs.cloudflare.com/ajax/libs/';
    const CDN_HTTPS_URL = 'https://cdnjs.cloudflare.com/ajax/libs/';

    /**
     * Search by packages
     *
     * @param string $query
     * @param array  $fields
     * @return mixed
     */
    public function search($query, $fields = ['version','description', 'homepage', 'keywords', 'maintainers','assets'])
    {
        $rawResult = file_get_contents($this->buildApiUrl($query, $fields));
        $result = json_decode($rawResult, true);

        return $result['results'];
    }

    /**
     * Return cdn URL for given package, version and fileName. And check that it's actually on CDN.
     *
     * @param string $packageName
     * @param string $version
     * @param string $fileName
     *
     * @param string $protocol
     * @return string
     * @throws \Exception
     */
    public function getCdnUrl($packageName, $version, $fileName, $protocol = 'http')
    {
        $results = $this->search($packageName, ['assets']);

        foreach ($results as $result) {
            foreach($result['assets'] as $asset) {
                if($asset['version'] == $version) {
                    foreach($asset['files'] as $file) {
                        if($file['name'] == $fileName) {
                            return self::buildCdnUrl($packageName, $version, $fileName, $protocol);
                        }
                    }
                }
            }
        }

        throw new \Exception(
            sprintf(
                "Given package (\"%s\") was not found in version \"%s\" and filename \"%s\" in CdnJs.",
                $packageName,
                $version,
                $fileName
            )
        );
    }

    /**
     * Build CDN url, without checking that asset is actually on CDN.
     *
     * @param $packageName
     * @param $version
     * @param $fileName
     * @param $protocol
     * @return string
     */
    public static function buildCdnUrl($packageName, $version, $fileName, $protocol)
    {
        $suffix = implode('/', [$packageName, $version, $fileName]);

        return $protocol == 'http' ? self::CDN_HTTP_URL.'/'.$suffix : self::CDN_HTTPS_URL.'/'.$suffix;
    }

    /**
     * @param $query
     * @param $fields
     * @return string
     */
    protected function buildApiUrl($query, $fields)
    {
        return self::API_URL.http_build_query(['search' => $query, 'fields' => implode(',', $fields)]);
    }
}