#!/usr/bin/php
<?php

require_once 'common.php';
assertCli();

/**
 * Flushes the default HTMLDefinition serial cache
 * @param Accepts one argument, cache type to flush; otherwise flushes all
 *      the caches.
 */

echo "Flushing cache... \n";

require_once(dirname(__FILE__) . '/../library/HTMLPurifier.auto.php');

$config = HTMLPurifier_Config::createDefault();

$names = array('HTML', 'CSS', 'URI', 'Test');
if (isset($argv[1])) {
    if (in_array($argv[1], $names)) {
        $names = array($argv[1]);
    } else {
        echo "Did not recognized cache parameter {$argv[1]} as valid cache, aborting.\n";
        exit;
    }
}

foreach ($names as $name) {
    echo " - Flushing $name\n";
    $cache = new HTMLPurifier_DefinitionCache_Serializer($name);
    $cache->flush($config);
}

echo 'Cache flushed successfully.';

