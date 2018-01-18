<?php

namespace WikiSnakr;

use Psr\Container\ContainerInterface;

/**
 * Interface FactoryInterface
 * @package WikiSnakr
 */
interface FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param array $options
     */
    public function __invoke(
        ContainerInterface $container, ?array $options = null
    );
}
