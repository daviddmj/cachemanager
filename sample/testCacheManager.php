<?php
/**
 * Cache Manager / Cache File sample code
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

require_once(dirname(__FILE__) . '/../autoload.php');

use cache\manager\CacheManager;
use cache\file\CacheFile;
use cache\search\textSearchProcessor;
use cache\search\arraySearchProcessor;

$cacheFileHTML = new CacheFile('html_content');
$cacheFileHTML->setCacheDirectory(dirname(__FILE__).'/../cache', true);

$cacheFileArray = new CacheFile('array_content');
$cacheFileArray->setCacheDirectory(dirname(__FILE__).'/../cache', true);

$cacheManager = new CacheManager(
    [
        new textSearchProcessor(),
        new arraySearchProcessor()
    ]
);

$cacheManager
    ->setCacheFiles(
        [
            $cacheFileHTML,
            $cacheFileArray
        ]
    )
    ->deleteFiles();

if (!$cacheFileHTML->hasContent()) {
    $cacheFileHTML->setContent('This is a sample text');
}

if (!$cacheFileArray->hasContent()) {
    $cacheFileArray->setContent(
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

$cacheFilesWithGoogle = $cacheManager->findCacheFilesMatching('sample text');
$cacheFilesWithDataKey = $cacheManager->findCacheFilesMatching('nested_key');

$cacheFiles = array_merge($cacheFilesWithGoogle, $cacheFilesWithDataKey);

/** @var CacheFile $cacheFile */
foreach ($cacheFiles as $cacheFile) {
    var_dump($cacheFile->getContent());
}