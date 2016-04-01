<?php
/**
 * Autoload initialization
 */

require_once(dirname(__FILE__) . '/src/managers/AutoloadManager.php');

$autoloadManager = new AutoloadManager();
$autoloadManager->addFolder(dirname(__FILE__).'/src/');
$autoloadManager->register();
