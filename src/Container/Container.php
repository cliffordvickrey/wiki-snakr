<?php

namespace WikiSnakr\Container;

use Psr\Container\ContainerInterface;

/**
 * Class Container
 * @package WikiSnakr\Container
 */
class Container implements ContainerInterface
{
    /**
     * @var array
     */
    protected static $factories = [];

    /**
     * @var array
     */
    protected static $deps = [];

    /**
     * Container constructor.
     * @param array $factories
     */
    public function __construct(array $factories)
    {
        self::$factories = $factories;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get($id)
    {
        if (array_key_exists($id, self::$deps)) {
            return self::$deps[$id];
        }

        if (!array_key_exists($id, self::$factories)) {
            throw new NotFoundException(
                sprintf("No factory provided for service '%s'", $id)
            );
        }

        $factoryName = self::$factories[$id];

        try {
            $factory = new $factoryName();
            return self::$deps[$id] = $factory($this);
        } catch (\Exception $e) {
            throw new ContainerException(
                sprintf(
                    "Factory '%s' unable to create service '%s': %s",
                    $factoryName, $id, $e->getMessage()
                )
            );
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id) : bool
    {
        return
            array_key_exists($id, self::$deps) ||
            array_key_exists($id, self::$factories);
    }

    /**
     * @param string $id
     * @param $dep
     */
    public function set(string $id, $dep) : void
    {
        self::$deps[$id] = $dep;
    }
}
