<?php

namespace cache\search\processors;

use cache\interfaces\SearchProcessorInterface;

/**
 * Class arraySearch
 * @package cache\search
 */
class ArraySearchProcessor implements SearchProcessorInterface
{
    /**
     * @return string
     */
    function getName()
    {
        return 'array_search';
    }

    /**
     * @param $content
     * @return bool
     */
    function isSupported($content)
    {
        return 'array' == gettype($content);
    }

    /**
     * @param $needle
     * @param $content
     * @return mixed|null
     */
    function search($needle, $content)
    {
        if (!$this->isSupported($content)) return null;

        $iterator = new \RecursiveArrayIterator($content);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }
    }
}