<?php

namespace WikiSnakr\Parser;

use WikiSnakr\FactoryInterface;
use WikiSnakr\Reader\ReaderInterface;
use Psr\Container\ContainerInterface;

/**
 * Class ParserFactory
 * @package WikiSnakr\Parser
 */
class ParserFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param array|null $options
     * @return Parser
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container, ?array $options = null
    )
    {
        if ($options === null) {
            $config = $container->get('config');
            $options = $config[ParserInterface::class];
        }

        return new Parser(
            $container->get(ReaderInterface::class), $options
        );
    }
}
