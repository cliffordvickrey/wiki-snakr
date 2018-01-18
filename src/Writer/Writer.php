<?php

namespace WikiSnakr\Writer;

/**
 * Class Writer
 * @package WikiSnakr\Writer
 */
class Writer implements WriterInterface
{
    /**
     * @var string
     */
    protected $filename = 'output.csv';

    /**
     * @var bool
     */
    protected $useBom = true;

    /**
     * Writer constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['filename'])) {
            $this->filename = $options['filename'];
        }

        if (isset($options['use_bom'])) {
            $this->useBom = $options['use_bom'];
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function write(array $data) : bool
    {
        if (!$this->isArrayMultiDimensional($data)) {
            $data = [$data];
        }

        $headers = $this->getHeaders(
            $headerInfo = $this->getHeaderInfo($data)
        );

        $output = [$headers];

        foreach ($data as $row) {
            $newRow = array_fill(0, count($headers), null);

            foreach ($row as $col => $val) {
                if ($col == 'qid') {
                    $normalizedCol = 'qid';
                } else {
                    $normalizedCol = $this->mapColumnToHeader(
                        $col, $headerInfo
                    );
                }
                $newRow[array_search($normalizedCol, $headers)] = $val;
            }

            $output[] = $newRow;
        }

        return $this->writeCsv($output);
    }

    /**
     * @param array $arr
     * @return bool
     */
    protected function writeCsv(array $arr) : bool
    {
        if ($fp = fopen($this->filename, 'w')) {
            if ($this->useBom) {
                fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
            }

            foreach ($arr as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $arr
     * @return bool
     */
    protected function isArrayMultiDimensional(array $arr) : bool
    {
        return count($arr) != count($arr, COUNT_RECURSIVE);
    }

    /**
     * @param array $headerInfo
     * @return array
     */
    protected function getHeaders(array $headerInfo) : array
    {
        $headers = [];

        foreach ($headerInfo as $name => $info) {
            $max = $info['max'];
            $qualifiers = $info['qualifiers'];

            $zeroPadding = strlen((string) $max);

            for ($i = 1; $i <= $max; $i++) {
                if ($max > 1) {
                    $suffix = sprintf("_%0{$zeroPadding}d", $i);
                } else {
                    $suffix = '';
                }

                $varName = sprintf(
                    '%s%s',
                    $name,
                    $suffix
                );

                $headers[] = $varName;

                if (in_array($i, $qualifiers)) {
                    $qualifierVarName = sprintf(
                        '%s%s_qualifier',
                        $name,
                        $suffix
                    );
                    $headers[] = $qualifierVarName;
                }
            }
        }

        return $headers;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getHeaderInfo(array $data) : array
    {
        $headers = call_user_func_array(
            'array_merge', array_map('array_keys', $data)
        );

        $headerInfo = [];

        foreach ($headers as $header) {
            if ($header == 'qid') {
                continue;
            }

            $parts = explode('_', $header);

            $count = (int) array_pop($parts);

            end($parts);
            $hasQualifier = (current($parts) == 'qualifier');

            if ($hasQualifier) {
                array_pop($parts);
            }

            $var = implode('_', $parts);

            if (!isset($headerInfo[$var])) {
                $headerInfo[$var] = ['max' => $count, 'qualifiers' => []];
            } else {
                if ($count > $headerInfo[$var]['max']) {
                    $headerInfo[$var]['max'] = $count;
                }
            }

            if ($hasQualifier) {
                $headerInfo[$var]['qualifiers'][] = $count;
            }
        }

        ksort($headerInfo);

        $headerInfo = array_merge(
            ['qid' => ['max' => 1, 'qualifiers' => []]], $headerInfo
        );

        return $headerInfo;
    }

    /**
     * @param string $column
     * @param array $headerInfo
     * @return string
     */
    protected function mapColumnToHeader(string $column, array $headerInfo) :
    string
    {
        $parts = explode('_', $column);

        $count = (int) array_pop($parts);

        end($parts);
        $hasQualifier = (current($parts) == 'qualifier');

        if ($hasQualifier) {
            array_pop($parts);
        }

        $var = implode('_', $parts);

        $max = $headerInfo[$var]['max'];
        $zeroPadding = strlen((string) $max);

        if ($max > 1) {
            $suffix = sprintf("_%0{$zeroPadding}d", $count);
        } else {
            $suffix = '';
        }

        if ($hasQualifier) {
            $format = '%s%s_qualifier';
        } else {
            $format = '%s%s';
        }

        return sprintf(
            $format,
            $var,
            $suffix
        );
    }
}
