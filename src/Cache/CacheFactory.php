<?php

namespace WikiSnakr\Cache;

use WikiSnakr\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Class CacheFactory
 * @package WikiSnakr\Cache
 */
class CacheFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param array|null $options
     * @return Cache
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container, ?array $options = null
    )
    {
        if ($options === null) {
            $config = $container->get('config');
            $options = $config[CacheInterface::class];
        }

        return new Cache($options);
    }
}
