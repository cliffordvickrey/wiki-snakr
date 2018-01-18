<?php

namespace WikiSnakr\Reader;

/**
 * Interface ReaderInterface
 * @package WikiSnakr\Reader
 */
interface ReaderInterface
{
    /**
     * @param string $id
     * @return array
     * @throws ReaderException
     */
    public function read(string $id) : array;
}
