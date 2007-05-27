#!/usr/bin/php
<?php

/**
 * Flushes the HTMLDefinition serial cache
 */

if (php_sapi_name() != 'cli') {
    echo 'Script cannot be called from web-browser.';
    exit;
}

echo 'Flushing cache... ';

require_once(dirname(__FILE__) . '/../library/HTMLPurifier.auto.php');

$cache = new HTMLPurifier_DefinitionCache_Serializer('HTML');
$cache->flush();

echo 'Cache flushed successfully.';

?>