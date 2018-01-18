<?php

namespace WikiSnakr;

/**
 * Interface ConfigInterface
 * @package WikiSnakr
 */
interface ConfigInterface
{
    /**
     * @return string
     */
    public function getCacheDir() : string;

    /**
     * @return array
     */
    public function getDependencies() : array;

    /**
     * @return string
     */
    public function getLocale() : string;

    /**
     * @return bool
     */
    public function getUseBom() : bool;

    /**
     * @return string
     */
    public function getWikiDataUrl() : string;

    /**
     * @return string
     */
    public function getWikiUrlFormat() : string;

    /**
     * @return string
     */
    public function getWriterFilename() : string;
}
