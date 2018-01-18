<?php

/**
 * Demo of the WikiSnakr package. Downloads, parses, and writes the
 * demographic traits of 10 American politicians
 *
 */

chdir(__DIR__);

// include this for PSR-4 autoloading
require_once('../vendor/autoload.php');

if (!is_dir('cache')) {
    mkdir('cache');
}

$wikiSnakr = new \WikiSnakr\WikiSnakr();

$ids = [
    'Q1124',    // Bill Clinton
    'Q76',      // Barack Obama
    'Q9960',    // Ronald Reagan
    'Q207',     // George W. Bush
    'Q19673',   // Al Gore
    'Q23685',   // Jimmy Carter
    'Q23505',   // George H. W. Bush
    'Q1282411', // Ed Markey
    'Q3182488'  // Bill Young
];

$data = $wikiSnakr->parseMultiple($ids);
$wikiSnakr->write($data);
