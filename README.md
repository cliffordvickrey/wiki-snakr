# WikiSnakr

Cleanly read, parse, and write [WikiData entities](https://www.wikidata.org/wiki/Wikidata:Introduction) in PHP. No need for any understanding of WikiData object structure or MediaWiki API calls!

## Overview

The application consists of:

- A Reader: Downloads objects from the WikiData repository
- A Cache: A PSR-6 cache implementation that stores the items, properties, and values contained on WikiData pages
- A Parser: Parses an entity or array of entities by their QID (which Wikipedia pages link to), iterating through all "claims" about an entity in its "snak". The complicated object structure is simplified to into simple `key => value` pairs of properties. Optionally stores all qualifiers to claims (e.g. the fact that Tom Foley was House Speaker is a "claim"; the fact that he started on 6\6\1989 is a qualifier) in the array
- A Writer: A simple class that saves WikiData objects to a readable comma-delimited files

The application was written for the purpose of fetching bulk data about American politicians, and many design choices reflected my specific needs. For instance, the need to know politicians' birth state and state of residence drove led me to append state postal codes to American placenames. The Datetime parser strips out second/hour/minute date formatting when unused, as well as plus sign prefixes denoting A.D. dates, so as to (e.g.) produce clean and sortable strings of birth years.

Improving or replacing the functionality of specific components should be easy. Also, each class follows the dependency injection pattern, in case anyone wanted to use Pimple/etc. to incorporate my crummy code into their web application :-)

## Usage

For ease of use, the application's dependencies are stored in the `WikiSnakr` container.

`$wikiSnakr = new \WikiSnakr\WikiSnakr();`

The application accepts an instance of `\WikiSnakr\ConfigInterface`; if none provided, it defaults to `\WikiSnakr\Config` 

The Config object provides the following values:

- `cacheDir`: the path where the Cache will save the likely thousands of JSON files needed to parse WikiData objects. Defaults to "cache"
- `dependencies`: an array that maps interfaces to fully qualified class names, in case you want to swap out any dependencies
- `locale`: locale to use when parsing multilingual text. Defaults to English ("en")
- `useBom`: whether or not the writer will append output with a UTF-8 byte order mark. Defaults to true
- `wikiDataUrl`: URL of the WikiData repository. Defaults to `https://www.wikidata.org/wiki/Special:EntityData/`
- `WikiDataUrlFormat`: format string for a WikiData JSON object. First parameter is the WikiData URL; second parameter is the entity ID. Defaults to `%s%s.json`
- `writerFileName`: name of the CSV to output to. Defaults to `output.csv`

Changing the configuration is simple:

```php
$config = new \WikiSnakr\Config();
$config->setCacheDir('bob');
 ```
 
 `example/Example.php` contains a simple example of parsing information about a few notable politicians:
 
 ```php
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
```

See `output.csv` for an example of parsed detail from a bulk operation.

## Requirements

- PHP 7.1
- PHP cURL extension enabled
- That's it!

## Todo

Implement bulk fetching of WikiData IDs from Wikipedia pages
