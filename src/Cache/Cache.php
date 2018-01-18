<?php

namespace WikiSnakr\Cache;

use Psr\SimpleCache\CacheInterface;

/**
 * Class Cache
 * @package WikiSnakr\Cache
 */
class Cache implements CacheInterface
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * Cache constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['cache_dir'])) {
            $this->cacheDir = $options['cache_dir'];
        }

        $this->cacheDir = rtrim($this->cacheDir, '/');
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if (file_exists($fileName = $this->getFileNameFromKey($key))) {
            $contents = file_get_contents($fileName);

            return json_decode($contents, true);
        }
        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        if (!is_scalar($value)) {
            $value = json_encode($value);
        }
        return (bool) file_put_contents(
            $this->getFileNameFromKey($key), $value
        );
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        if (file_exists($fileName = $this->getFileNameFromKey($key))) {
            return unlink($fileName);
        }
        return false;
    }

    /**
     * @param iterable $keys
     * @param null $default
     * @return array|iterable
     * @throws InvalidArgumentException
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_iterable($keys)) {
            throw new InvalidArgumentException(
                'Data dictionary must be iterable'
            );
        }

        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $this->get($key, $default);
        }
        return $return;
    }

    /**
     * @param iterable $values
     * @param null $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!is_iterable($values)) {
            throw new InvalidArgumentException(
                'Data dictionary must be iterable'
            );
        }

        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value) && $success) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $globbed = glob(sprintf('%s/*.json', $this->cacheDir));

        array_map('unlink', $globbed);

        return true;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key) : bool
    {
        return file_exists($this->getFileNameFromKey($key));
    }

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key) && $success) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getFileNameFromKey(string $key) : string
    {
        return sprintf(
            '%s/%s.json', $this->cacheDir, $this->sanitizeKey($key)
        );
    }

    /**
     * @param string $key
     * @return string
     */
    protected function sanitizeKey(string $key) : string
    {
        return preg_replace('/[^a-zA-Z0-9]+/', '', $key);
    }
}
