<?php

namespace WikiSnakr\Parser;

/**
 * Interface ParserInterface
 * @package WikiSnakr\Parser
 */
interface ParserInterface
{
    /**
     * @param string $id
     * @param bool $parseQualifiers
     * @return array
     */
    public function parse(string $id, bool $parseQualifiers = true) : array;

    /**
     * @param array $ids
     * @param bool $parseQualifiers
     * @return array
     */
    public function parseMultiple(
        array $ids, bool $parseQualifiers = true
    ) : array;
}
