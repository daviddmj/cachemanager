<?php

namespace cache\interfaces;

/**
 * Interface SearchInterface
 * @package cache\interfaces
 */
interface SearchProcessorInterface
{
    /**
     * @return string
     */
    function getName();

    /**
     * @param $content
     * @return mixed
     */
    function isEligible($content);

    /**
     * @param $needle
     * @param $content
     * @return mixed
     */
    function search($needle, $content);
}