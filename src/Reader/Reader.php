<?php

namespace WikiSnakr\Reader;

use Psr\SimpleCache\CacheInterface;

/**
 * Class Reader
 * @package WikiSnakr\Reader
 */
class Reader implements ReaderInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $wikiDataUrl =
        'https://www.wikidata.org/wiki/Special:EntityData/';

    /**
     * @var string
     */
    protected $wikiUrlFormat = '%s%s.json';

    /**
     * Reader constructor.
     * @param CacheInterface $cache
     * @param array $options
     */
    public function __construct(CacheInterface $cache, array $options = [])
    {
        $this->cache = $cache;

        if (isset($options['wiki_data_url'])) {
            $this->wikiDataUrl = $options['wiki_data_url'];
        }

        if (isset($options['wiki_url_format'])) {
            $this->wikiUrlFormat = $options['wiki_url_format'];
        }
    }

    /**
     * @param string $id
     * @return array
     * @throws ReaderException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function read(string $id) : array
    {
        if ($cached = $this->cache->get($id)) {
            return $cached;
        }

        $data = $this->download(
            sprintf(
                $this->wikiUrlFormat,
                $this->wikiDataUrl,
                urlencode($id)
            )
        );

        $this->cache->set($id, $data);

        return json_decode($data, true);
    }

    /**
     * @param string $url
     * @return mixed
     * @throws ReaderException
     */
    protected function download(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);

        if ($errNo = curl_errno($ch)) {
            $error = curl_strerror($errNo);
        } else {
            $error = null;
        }

        curl_close($ch);

        if ($error) {
            throw new ReaderException(
                sprintf(
                    'Could not download data from "%s"; error: %s',
                    $url, $error
                )
            );
        }

        return $response;
    }
}
