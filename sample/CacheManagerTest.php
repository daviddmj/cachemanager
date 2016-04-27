<?php
/**
 * Cache Manager / Cache Object test code
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

require_once(dirname(__FILE__) . '/../autoload.php');

use cache\manager\CacheManager;
use cache\file\CacheObject;
use cache\search\TextSearchProcessor;
use cache\search\ArraySearchProcessor;

$cacheHTMLObject = new CacheObject('html_content');
$cacheHTMLObject->setCacheDirectory(dirname(__FILE__).'/../cache', true);

$cacheArrayObject = new CacheObject('array_content');
$cacheArrayObject->setCacheDirectory(dirname(__FILE__).'/../cache', true);

$cacheManager = new CacheManager(
    [
        new TextSearchProcessor(),
        new ArraySearchProcessor()
    ]
);

$cacheManager
    ->setCacheObjects(
        [
            $cacheHTMLObject,
            $cacheArrayObject
        ]
    )
    ->deleteFiles()
;

if (!$cacheHTMLObject->hasContent()) {
    $cacheHTMLObject->setContent(file_get_contents('http://www.google.com/'));
}

if (!$cacheArrayObject->hasContent()) {
    $cacheArrayObject->setContent(
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

$cacheManager->flush();

$cacheFilesWithGoogle = $cacheManager->findCacheObjectsMatching('google');
$cacheFilesWithDataKey = $cacheManager->findCacheObjectsMatching('nested_key');

$cacheObjects = array_merge($cacheFilesWithGoogle, $cacheFilesWithDataKey);

/** @var CacheObject $cacheObject */
foreach ($cacheObjects as $cacheObject) {
    var_dump(
        [
            $cacheObject->getName() => $cacheObject->getContent()
        ]
    );
}