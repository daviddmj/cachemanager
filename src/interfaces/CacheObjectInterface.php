<?php
/**
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

namespace cache\interfaces;

/**
 * Interface CacheObjectInterface
 */
interface CacheObjectInterface
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

    /**
     * @return mixed
     */
    function writeFile();

    /**
     * @return mixed
     */
    function deleteFile();

    /**
     * @return bool
     */
    function isModified();

    /**
     * @return mixed
     */
    function getCacheManager();

    /**
     * @param $cacheManager
     * @return mixed
     */
    function setCacheManager($cacheManager);
}