<?php

namespace WikiSnakr\Writer;

use WikiSnakr\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Class WriterFactory
 * @package WikiSnakr\Writer
 */
class WriterFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param array|null $options
     * @return Writer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container, ?array $options = null
    )
    {
        if ($options === null) {
            $config = $container->get('config');
            $options = $config[WriterInterface::class];
        }

        return new Writer($options);
    }
}
