<?php

namespace WikiSnakr;

use WikiSnakr\Container\Container;
use WikiSnakr\Container\ContainerException;
use WikiSnakr\Container\NotFoundException;
use WikiSnakr\Parser\ParserInterface;
use WikiSnakr\Reader\ReaderInterface;
use WikiSnakr\Writer\WriterInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class WikiSnakr
 * @package WikiSnakr
 */
class WikiSnakr
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * WikiSnakr constructor.
     * @param ConfigInterface|null $config
     */
    public function __construct(?ConfigInterface $config = null)
    {
        if ($config === null) {
            $config = new Config();
        }

        $this->container = new Container($config->getDependencies());

        $configService = [
            CacheInterface::class => [
                'cache_dir' => $config->getCacheDir()
            ],
            ParserInterface::class => [
                'locale' => $config->getLocale()
            ],
            ReaderInterface::class => [
                'wiki_data_url' => $config->getWikiDataUrl(),
                'wiki_url_format' => $config->getWikiUrlFormat()
            ],
            WriterInterface::class => [
                'filename' => $config->getWriterFilename(),
                'use_bom' => $config->getUseBom()
            ]
        ];

        $this->container->set('config', $configService);
    }

    /**
     * @param string $id
     * @param bool $parseQualifiers
     * @return array
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function parse(string $id, bool $parseQualifiers = true) : array
    {
        return $this->getParser()->parse($id, $parseQualifiers);
    }

    /**
     * @param array $ids
     * @param bool $parseQualifiers
     * @return array
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function parseMultiple(
        array $ids, bool $parseQualifiers = true
    ) : array
    {
        return $this->getParser()->parseMultiple($ids, $parseQualifiers);
    }

    /**
     * @return ParserInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function getParser() : ParserInterface
    {
        if ($this->parser === null) {
            return $this->parser =
                $this->container->get(ParserInterface::class);
        }
        return $this->parser;
    }

    /**
     * @param array $data
     * @return bool
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function write(array $data) : bool
    {
        return $this->getWriter()->write($data);
    }

    /**
     * @return WriterInterface
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function getWriter() : WriterInterface
    {
        if ($this->writer === null) {
            return $this->writer =
                $this->container->get(WriterInterface::class);
        }
        return $this->writer;
    }
}
