<?php

namespace WikiSnakr\Reader;

use WikiSnakr\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class ReaderFactory
 * @package WikiSnakr\Reader
 */
class ReaderFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param array|null $options
     * @return Reader
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container, ?array $options = null
    )
    {
        if ($options === null) {
            $config = $container->get('config');
            $options = $config[ReaderInterface::class];
        }

        return new Reader($container->get(CacheInterface::class), $options);
    }
}
