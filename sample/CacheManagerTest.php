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
use cache\search\ObjectSearchProcessor;

$cacheHTMLObject = new CacheObject('html_content');
$cacheHTMLObject->setCacheDirectory(dirname(__FILE__).'/../cache', CacheObject::FORCE_CREATE_DIR);

$cacheArrayObject = new CacheObject('array_content');
$cacheArrayObject->setCacheDirectory(dirname(__FILE__).'/../cache', CacheObject::FORCE_CREATE_DIR);

$cacheObjectObject = new CacheObject('object_content');
$cacheObjectObject->setCacheDirectory(dirname(__FILE__).'/../cache', CacheObject::FORCE_CREATE_DIR);


$cacheManager = new CacheManager(
    [
        new TextSearchProcessor(),
        new ArraySearchProcessor(),
        new ObjectSearchProcessor()
    ]
);

$cacheManager
    ->setCacheObjects(
        [
            $cacheHTMLObject,
            $cacheArrayObject,
            $cacheObjectObject
        ]
    )
    ->deleteFiles()
;

if (!$cacheHTMLObject->hasContent()) {
    $cacheHTMLObject->setContent(file_get_contents('http://www.google.com/'), CacheObject::COMPRESSED_DATA);
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

if (!$cacheObjectObject->hasContent()) {
    $cacheObjectObject->setContent(new DateTime(), CacheObject::COMPRESSED_DATA);
}

$cacheManager->flush();

$cacheFilesWithGoogle = $cacheManager->findCacheObjectsMatching('google');
$cacheFilesWithDataKey = $cacheManager->findCacheObjectsMatching('nested_key');
$cacheFilesWithProperty = $cacheManager->findCacheObjectsMatching('timezone');

$cacheObjects = array_merge($cacheFilesWithGoogle, $cacheFilesWithDataKey, $cacheFilesWithProperty);

/** @var CacheObject $cacheObject */
foreach ($cacheObjects as $cacheObject) {
    var_dump(
        [
            $cacheObject->getName() => $cacheObject->getContent()
        ]
    );
}