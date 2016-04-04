<?php

namespace cache\interfaces;

/**
 * Interface SearchInterface
 * @package cache\interfaces
 */
interface SearchInterface
{
    /**
     * @param $needle
     * @param $content
     * @return mixed
     */
    static function search($needle, $content);
}