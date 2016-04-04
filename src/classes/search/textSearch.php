<?php

namespace cache\search;

use cache\interfaces\SearchInterface;

/**
 * Class textSearch
 * @package cache\search
 */
class textSearch implements SearchInterface
{
    /**
     * @param $needle
     * @param $content
     * @return mixed
     */
    static function search($needle, $content)
    {
        return preg_match('#\b' . preg_quote($needle, '#') . '\b#i', $content);
    }
}