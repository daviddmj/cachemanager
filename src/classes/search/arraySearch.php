<?php

namespace cache\search;

use cache\interfaces\SearchProcessorInterface;

/**
 * Class arraySearch
 * @package cache\search
 */
class arraySearchProcessor implements SearchProcessorInterface
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
    function isEligible($content)
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
        if (!$this->isEligible($content)) return null;

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