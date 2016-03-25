<?php
/**
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

namespace cache\file;

/**
 * Interface CacheFileInterface
 */
interface CacheFileInterface
{
    /**
     * @return string
     */
    function getName();

    /**
     * @return string
     */
    function getExpirationDate();

    /**
     * @return string
     */
    function getCacheDirectory();

    /**
     * @param string $cacheDirectory
     */
    function setCacheDirectory($cacheDirectory);

    /**
     * @return string
     */
    function getCacheFile();

    /**
     * @param string $cacheFile
     */
    function setCacheFile($cacheFile);

    /**
     * @return mixed
     */
    function getContent();

    /**
     * @param mixed $content
     */
    function setContent($content);
}