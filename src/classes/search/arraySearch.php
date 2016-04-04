<?php

namespace cache\search;

use cache\interfaces\SearchInterface;

/**
 * Class arraySearch
 * @package cache\search
 */
class arraySearch implements SearchInterface
{
    /**
     * @param $needle
     * @param $content
     * @return mixed|null
     */
    static function search($needle, $content)
    {
        $iterator  = new \RecursiveArrayIterator($content);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }
        return null;
    }
}