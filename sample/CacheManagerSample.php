<?php
/**
 * Cache Manager / Cache Object usage sample code
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

require_once(dirname(__FILE__) . '/../autoload.php');

use cache\manager\CacheManager;
use cache\file\CacheObject;
use cache\search\processors\TextSearchProcessor;
use cache\search\processors\ArraySearchProcessor;
use cache\search\processors\ObjectSearchProcessor;
use cache\search\results\SearchResultObject;

/**
 * Create CacheManager instance and related search processors and cache objects to work with.
 * Delete existing cache files if present
 */
$cacheManager = (new CacheManager())
    ->addSearchProcessor(new TextSearchProcessor())
    ->addSearchProcessor(new ArraySearchProcessor())
    ->addSearchProcessor(new ObjectSearchProcessor())
    ->addCacheObject((new CacheObject('html_content'))
        ->setCacheDirectory(dirname(__FILE__).'/../cache', CacheObject::FORCE_CREATE_DIR))
    ->addCacheObject((new CacheObject('array_content'))
        ->setCacheDirectory(dirname(__FILE__).'/../cache', CacheObject::FORCE_CREATE_DIR))
    ->addCacheObject((new CacheObject('object_content'))
        ->setCacheDirectory(dirname(__FILE__).'/../cache', CacheObject::FORCE_CREATE_DIR))
    ->deleteFiles();

/**
 * Set html cache object with random content and compress data
 */
if (!$cacheManager->getCacheObject('html_content')->hasContent()) {
    $cacheManager->getCacheObject('html_content')->setContent(
        file_get_contents('http://www.google.com/'),
        CacheObject::COMPRESSED_DATA
    );
}

/**
 * Set array cache object with random content
 */
if (!$cacheManager->getCacheObject('array_content')->hasContent()) {
    $cacheManager->getCacheObject('array_content')->setContent(
        [
            'key_without_nested_data' => 'random data',
            'key_with_nested_data' => [
                'nested_array' => [
                    'nested_key' => 'nested value'
                ]
            ]
        ]
    );
}

/**
 * Store html cache object in object content cache object and compress data
 */
if (!$cacheManager->getCacheObject('object_content')->hasContent()) {
    $cacheManager->getCacheObject('object_content')->setContent(
        $cacheManager->getCacheObject('html_content'),
        CacheObject::COMPRESSED_DATA
    );
}

/**
 * Flush cache objects to disk
 */
$cacheManager->flush();

/**
 * Find random content in all cache objects
 */
$cacheObjects = array_merge(
    $cacheManager->findCacheObjectsMatching('google'),
    $cacheManager->findCacheObjectsMatching('nested_key'),
    $cacheManager->findCacheObjectsMatching('getExpirationDate')
);

/** @var SearchResultObject $searchResult */
foreach ($cacheObjects as $searchResult) {
    var_dump(
        [
            $searchResult->getCacheObject()->getName() => $searchResult->getCacheObject()->getCacheDirectory()
        ]
    );
}