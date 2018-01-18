<?php

namespace WikiSnakr\Parser;

use WikiSnakr\Reader\ReaderInterface;
use WikiSnakr\Reader\ReaderException;

/**
 * Class Parser
 * @package WikiSnakr\Parser
 */
class Parser implements ParserInterface
{
    /**
     * @var int
     */
    const FIPS_PROPERTY = 'P774';

    /**
     * @var string
     */
    const FALLBACK_LOCALE = 'en';

    /**
     * @var string
     */
    const UNKNOWN_VALUE = 'unknown';

    /**
     * @var ReaderInterface
     */
    protected $reader;

    /**
     * @var string
     */
    protected $locale = 'en';

    /**
     * @var array
     */
    protected $mutators = [
        'globecoordinate' => 'parseGlobeCoordinate',
        'monolingualtext' => 'parseMonolingualText',
        'quantity' => 'parseQuantity',
        'time' => 'parseTime',
        'wikibase-entityid' => 'parseWikiBaseEntityId'
    ];

    /**
     * @var array
     */
    protected $fips = [
        1 => 'AL',
        2 => 'AK',
        60 => 'AS',
        4 => 'AZ',
        5 => 'AR',
        81 => 'Baker Island',
        6 => 'CA',
        8 => 'CO',
        9 => 'CT',
        10 => 'DE',
        11 => 'DC',
        12 => 'FL',
        64 => 'FM',
        13 => 'GA',
        66 => 'GU',
        15 => 'HI',
        84 => 'Howland Island',
        16 => 'ID',
        17 => 'IL',
        18 => 'IN',
        19 => 'IA',
        86 => 'Jarvis Island',
        67 => 'Johnston Atoll',
        20 => 'KS',
        21 => 'KY',
        89 => 'Kingman Reef',
        22 => 'LA',
        23 => 'ME',
        68 => 'MH',
        24 => 'MD',
        25 => 'MA',
        26 => 'MI',
        71 => 'Midway Islands',
        27 => 'MN',
        28 => 'MS',
        29 => 'MO',
        30 => 'MT',
        76 => 'Navassa Island',
        31 => 'NE',
        32 => 'NV',
        33 => 'NH',
        34 => 'NJ',
        35 => 'NM',
        36 => 'NY',
        37 => 'NC',
        38 => 'ND',
        69 => 'MP',
        39 => 'OH',
        40 => 'OK',
        41 => 'OR',
        70 => 'Palau',
        95 => 'Palmyra Atoll',
        42 => 'PA',
        72 => 'PT',
        44 => 'RI',
        45 => 'SC',
        46 => 'SD',
        47 => 'TN',
        48 => 'TX',
        74 => 'U.S. Minor Outlying Islands',
        49 => 'UT',
        50 => 'VT',
        51 => 'VA',
        78 => 'VI',
        79 => 'Wake Island',
        53 => 'WA',
        54 => 'WV',
        55 => 'WI',
        56 => 'WY'
    ];

    /**
     * Parser constructor.
     * @param ReaderInterface $reader
     * @param array $options
     */
    public function __construct(
        ReaderInterface $reader, array $options = []
    )
    {
        $this->reader = $reader;

        if (isset($options['locale'])) {
            $this->locale = $options['locale'];
        }
    }

    /**
     * @param string $id
     * @param bool $parseQualifiers
     * @return array
     * @throws ReaderException
     */
    public function parse(string $id, bool $parseQualifiers = true) : array
    {
        $data = $this->reader->read($id);

        if (empty($claims = $data['entities'][$id]['claims'] ?? [])) {
            return [];
        }

        $parsed = ['qid' => $id];

        foreach ($claims as $propertyId => $claim) {
            $zeroPadding = strlen((string) count($claims)) ?: 1;

            foreach ($claim as $claimNumber => $claimData) {
                $varName = $this->getVarName(
                    $propertyId, $claimNumber + 1, $zeroPadding
                );

                /**
                 * lookin' like a snak RN
                 */

                $mainSnak = $claimData['mainsnak'] ?? null;

                if ($parseQualifiers) {
                    $qualifiers = $claimData['qualifiers'] ?? null;
                } else {
                    $qualifiers = null;
                }

                if ($mainSnak !== null) {
                    $parsed[$varName] = $this->parseSnak($mainSnak);
                }

                if ($qualifiers !== null) {
                    $parsedQualifiers = [];

                    $qualifierName =
                        $this->getQualifierNameFromVarName($varName);

                    foreach ($qualifiers as $qualifierId => $qualifierData) {
                        $baseKey = $this->getEntityName($qualifierId);

                        $isMultiple = count($qualifierData) > 1;

                        foreach (
                            $qualifierData as
                            $qualifierNumber => $qualifierSnak
                        ) {
                            if ($isMultiple) {
                                $key = sprintf(
                                    '%s %d',
                                    $baseKey,
                                    $qualifierNumber
                                );
                            } else {
                                $key = $baseKey;
                            }

                            $parsedQualifiers[] = sprintf(
                                '%s = %s',
                                $key,
                                $this->parseSnak($qualifierSnak)
                            );
                        }
                    }

                    $parsed[$qualifierName] = implode('; ', $parsedQualifiers);
                }
            }
        }

        return $parsed;
    }

    /**
     * @param array $ids
     * @param bool $parseQualifiers
     * @return array
     * @throws ReaderException
     */
    public function parseMultiple(
        array $ids, bool $parseQualifiers = true
    ): array
    {
        $parsed = [];
        foreach ($ids as $id) {
            $parsed[$id] = $this->parse($id, $parseQualifiers);
        }
        return $parsed;
    }

    /**
     * @param array $snak
     * @return string
     */
    protected function parseSnak(array $snak) : string
    {
        $snakType = $snak['snaktype'] ?? null;
        $dataValue = $snak['datavalue'] ?? null;

        if ($snakType == 'somevalue') {
            return self::UNKNOWN_VALUE;
        }

        if ($snakType == 'novalue' || $dataValue === null) {
            return '';
        }

        $type = $dataValue['type'] ?? null;
        $value = $dataValue['value'] ?? null;

        if ($value === null) {
            return '';
        }

        if (!$type) {
            $type = 'string';
        }

        if (isset($this->mutators[$type])) {
            $mutator = $this->mutators[$type];
            return $this->$mutator($value);
        }

        return $value;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function parseGlobeCoordinate(array $data) : string
    {
        return sprintf(
            'x = %s; y = %s', $data['latitude'], $data['longitude']
        );
    }

    /**
     * @param array $data
     * @return string
     */
    protected function parseMonoLingualText(array $data) : string
    {
        return $data['text'];
    }

    /**
     * @param array $data
     * @return string
     */
    protected function parseQuantity(array $data) : string
    {
        return preg_replace('/([^\-0-9\.,])/', '', $data['amount']);
    }

    /**
     * @param array $data
     * @return string
     */
    protected function parseTime(array $data) : string
    {
        $parts = explode('-', $data['time']);
        $year = array_shift($parts);

        $signage = substr($year, 0, 1) == '+' ? '' : '-';
        $year = (int) preg_replace('/[^0-9]/', '', $year);

        if ($inUnixEpoch = $year > 1970) {
            $tempYear = sprintf('%04d', $year);
        } else {
            $tempYear = $this->isLeapYear($year) ? '1972' : '1970';
        }

        $parts = array_merge([$tempYear], $parts);

        $dateTime = \DateTime::createFromFormat(
            'Y-m-d\TH:i:s+', $isoTime = implode('-', $parts)
        );

        if (strpos($isoTime, '00:00:00') === false) {
            $format = sprintf(
                '%sY-m-d H:i:s', $signage
            );
        } elseif (strpos($isoTime, '-00-00') !== false) {
            $format = sprintf(
                '%sY', $signage
            );
        } elseif (strpos($isoTime, '-00') !== false) {
            $format = sprintf(
                '%sY-m', $signage
            );
        } else {
            $format = sprintf(
                '%sY-m-d', $signage
            );
        }

        $date = $dateTime->format($format);

        if (!$inUnixEpoch) {
            $parts = explode('-', $date);
            $parts[0] = sprintf('%04d', $year);
            $date = implode('-', $parts);
        }

        return $date;
    }

    /**
     * @param int $year
     * @return bool
     */
    protected function isLeapYear(int $year) : bool
    {
        switch (true) {
            case ($year % 400) === 0:
                return true;
            case ($year % 100) === 0:
                return false;
            case ($year % 4) === 0:
                return true;
            default:
                return false;
        }
    }

    /**
     * @param array $data
     * @return string
     * @throws ReaderException
     */
    protected function parseWikiBaseEntityId(array $data) : string
    {
        $entityType = $data['entity-type'] ?? null;
        $id = $data['id'] ?? null;
        $numericId = $data['numeric-id'] ?? null;

        if ($id) {
            $value = $id;
        } elseif($numericId) {
            if ($entityType == 'prefix') {
                $prefix = 'P';
            } else {
                $prefix = 'Q';
            }
            
            $value = sprintf('%s%s', $prefix, $numericId);
        } else {
            return '';
        }
        
        return $this->getEntityName($value, true);
    }

    /**
     * @param string $propertyId
     * @param int $number
     * @param int $zeroPadding
     * @return string
     * @throws ReaderException
     */
    protected function getVarName(
        string $propertyId, int $number, int $zeroPadding = 2
    ) :
    string
    {
        return sprintf(
            '%s_%s',
            $this->normalizeName($this->getEntityName($propertyId)),
            sprintf("%0{$zeroPadding}d", $number)
        );
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizeName(string $name) : string
    {
        return preg_replace(
            '/[^\w\d_]+$/', '', str_replace([' ', '-'], '_', strtolower($name))
        );
    }

    /**
     * @param string $varName
     * @return string
     */
    protected function getQualifierNameFromVarName(string $varName) : string
    {
        $parts = explode('_', $varName);
        $number = array_pop($parts);
        $parts = array_merge($parts, ['qualifier', $number]);
        return implode('_', $parts);
    }

    /**
     * @param string $id
     * @param bool $addStateToName
     * @return string
     * @throws ReaderException
     */
    protected function getEntityName(
        string $id, bool $addStateToName = false
    ) : string
    {
        $data = $this->reader->read($id);

        $l = $this->locale;
        if (
            !($name =
                $data['entities'][$id]['labels'][$l]['value'] ??
                null
            )
        ) {
            $l = self::FALLBACK_LOCALE;
            if (
                !($name =
                    $data['entities'][$id]['labels'][$l]['value'] ??
                    null
                )
            ) {
                $name = '';
            }
        }

        if (
            $addStateToName &&
            $name &&
            strpos($name, ',') === false &&
            ($state = $this->extractStateFromData($data))
        ) {
            $name .= sprintf(', %s', $state);
        }

        return $name;
    }

    /**
     * @param array $data
     * @return string
     */
    protected function extractStateFromData(array $data) : string
    {
        $id = key($data['entities']);

        $fipsSnak =
            $data['entities'][$id]['claims'][self::FIPS_PROPERTY][0]
            ['mainsnak'] ?? null;

        if ($fipsSnak === null) {
            $state = '';
        } else {
            $qualifiedFips = $this->parseSnak($fipsSnak);
            $parts = explode('-', $qualifiedFips);
            $fips = $parts[0];

            if (strlen($fips) > 2) {
                $fips = substr($fips, 0, 2);
            }

            $state = $this->fips[$fips] ?? '';
        }

        return $state;
    }
}
