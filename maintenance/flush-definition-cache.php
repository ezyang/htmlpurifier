#!/usr/bin/php
<?php

/**
 * Flushes the default HTMLDefinition serial cache
 */

if (php_sapi_name() != 'cli') {
    echo 'Script cannot be called from web-browser.';
    exit;
}

echo "Flushing cache... \n";

require_once(dirname(__FILE__) . '/../library/HTMLPurifier.auto.php');

$config = HTMLPurifier_Config::createDefault();

//$names = array('HTML', 'CSS', 'URI', 'Test');
$names = array('URI');
foreach ($names as $name) {
    echo " - Flushing $name\n";
    $cache = new HTMLPurifier_DefinitionCache_Serializer($name);
    $cache->flush($config);
}

echo 'Cache flushed successfully.';

