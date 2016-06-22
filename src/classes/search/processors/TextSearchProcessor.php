<?php

namespace cache\search\processors;

use cache\interfaces\SearchProcessorInterface;

/**
 * Class textSearch
 * @package cache\search
 */
class TextSearchProcessor implements SearchProcessorInterface
{
    /**
     * @return string
     */
    function getName()
    {
        return 'text_search';
    }

    /**
     * @param $content
     * @return bool
     */
    function isSupported($content)
    {
        return 'string' == gettype($content);
    }

    /**
     * @param $needle
     * @param $content
     * @return mixed
     */
    function search($needle, $content)
    {
        if ($this->isSupported($content) && preg_match_all('#\b' . preg_quote($needle, '#') . '\b#i', $content, $matches)) {
            return [
                'needle' => $needle,
                'count' => count($matches[0])
            ];
        }

        return null;
    }
}