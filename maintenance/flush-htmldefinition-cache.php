#!/usr/bin/php
<?php

/**
 * Flushes the default HTMLDefinition serial cache
 */

if (php_sapi_name() != 'cli') {
    echo 'Script cannot be called from web-browser.';
    exit;
}

echo 'Flushing cache... ';

require_once(dirname(__FILE__) . '/../library/HTMLPurifier.auto.php');

$config = HTMLPurifier_Config::createDefault();

$cache = new HTMLPurifier_DefinitionCache_Serializer('HTML');
$cache->flush($config);

echo 'Cache flushed successfully.';

