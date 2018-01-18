<?php

namespace WikiSnakr;

use WikiSnakr\Cache\CacheFactory;
use WikiSnakr\Parser\ParserFactory;
use WikiSnakr\Parser\ParserInterface;
use WikiSnakr\Reader\ReaderFactory;
use WikiSnakr\Reader\ReaderInterface;
use WikiSnakr\Writer\WriterFactory;
use WikiSnakr\Writer\WriterInterface;
use Psr\SimpleCache\CacheInterface;

class Config implements ConfigInterface
{
    /**
     * @var string
     */
    protected $cacheDir = 'cache';

    /**
     * @var array 
     */
    protected $dependencies = [
        CacheInterface::class => CacheFactory::class,
        ParserInterface::class => ParserFactory::class,
        ReaderInterface::class => ReaderFactory::class,
        WriterInterface::class => WriterFactory::class
    ];
    
    /**
     * @var string
     */
    protected $locale = 'en';

    /**
     * @var bool
     */
    protected $useBom = true;

    /**
     * @var string
     */
    protected $wikiDataUrl =
        'https://www.wikidata.org/wiki/Special:EntityData/';

    /**
     * @var string
     */
    protected $wikiUrlFormat = '%s%s.json';

    /**
     * @var string
     */
    protected $writerFilename = 'output.csv';

    /**
     * @param string $cacheDir
     * @return Config
     */
    public function setCacheDir(string $cacheDir): Config
    {
        $this->cacheDir = $cacheDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @param array $dependencies
     * @return Config
     */
    public function setDependencies(array $dependencies): Config
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    /**
     * @param string $locale
     * @return Config
     */
    public function setLocale(string $locale): Config
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return bool
     */
    public function getUseBom(): bool
    {
        return $this->useBom;
    }

    /**
     * @param bool $useBom
     * @return Config
     */
    public function setUseBom(bool $useBom): Config
    {
        $this->useBom = $useBom;
        return $this;
    }

    /**
     * @return string
     */
    public function getWikiDataUrl(): string
    {
        return $this->wikiDataUrl;
    }

    /**
     * @param string $wikiDataUrl
     * @return Config
     */
    public function setWikiDataUrl(string $wikiDataUrl): Config
    {
        $this->wikiDataUrl = $wikiDataUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getWikiUrlFormat(): string
    {
        return $this->wikiUrlFormat;
    }

    /**
     * @param string $wikiUrlFormat
     * @return Config
     */
    public function setWikiUrlFormat(string $wikiUrlFormat): Config
    {
        $this->wikiUrlFormat = $wikiUrlFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function getWriterFilename(): string
    {
        return $this->writerFilename;
    }

    /**
     * @param string $writerFilename
     * @return Config
     */
    public function setWriterFilename(string $writerFilename): Config
    {
        $this->writerFilename = $writerFilename;
        return $this;
    }

    /**
     * @param string $className
     * @return Config
     */
    public function setCacheClass(string $className) : Config
    {
        $this->dependencies[CacheInterface::class] = $className;
        return $this;
    }

    /**
     * @param string $className
     * @return Config
     */
    public function setParserClass(string $className) : Config
    {
        $this->dependencies[ParserInterface::class] = $className;
        return $this;
    }

    /**
     * @param string $className
     * @return Config
     */
    public function setReaderClass(string $className) : Config
    {
        $this->dependencies[ReaderInterface::class] = $className;
        return $this;
    }

    /**
     * @param string $className
     * @return Config
     */
    public function setWriterClass(string $className) : Config
    {
        $this->dependencies[WriterInterface::class] = $className;
        return $this;
    }
}
