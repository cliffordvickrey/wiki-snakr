<?php

namespace WikiSnakr\Writer;

/**
 * Interface WriterInterface
 * @package WikiSnakr\Writer
 */
interface WriterInterface
{
    /**
     * @param array $data
     * @return bool
     */
    public function write(array $data) : bool;
}
