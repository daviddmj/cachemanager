<?php
/**
 * Cache Manager / Cache File sample code
 * @author David AFONSO <dafonso@prestaconcept.net>
 */

require_once(dirname(__FILE__).'/../classes/CacheFile.class.php');
require_once(dirname(__FILE__).'/../classes/CacheManager.class.php');

use cache\manager\CacheManager;
use cache\file\CacheFile;

$cacheFileHTML = new CacheFile('html_content');
$cacheFileHTML->setCacheDirectory(dirname(__FILE__).'/../cache', true);

$cacheFileArray = new CacheFile('array_content');
$cacheFileArray->setCacheDirectory(dirname(__FILE__).'/../cache', true);

$cacheManager = new CacheManager();

$cacheManager
    ->setCacheFiles(
        [
            $cacheFileHTML,
            $cacheFileArray
        ]
    )
    ->deleteFiles();

if (!$cacheFileHTML->hasContent()) {
    $cacheFileHTML->setContent('dfsdfsdfsd fsgndfgoidfndfogi fgfdgdfga');
}

if (!$cacheFileArray->hasContent()) {
    $cacheFileArray->setContent(
        [
            'data_key' => 'random data',
            'nested_data' => [
                'data_key_nested' => [
                    'over_nested' => 'nested value 2'
                ]
            ]
        ]
    );
}

$cacheManager->flush();

$cacheFilesWithGoogle = $cacheManager->findCacheFilesMatching('fgfdgdfga');
$cacheFilesWithDataKey = $cacheManager->findCacheFilesMatching('over_nested');

$cacheFiles = array_merge($cacheFilesWithGoogle, $cacheFilesWithDataKey);

/** @var CacheFile $cacheFile */
foreach ($cacheFiles as $cacheFile) {
    var_dump($cacheFile->getContent());
}