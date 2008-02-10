#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require_once 'common.php';
require_once '../library/HTMLPurifier.auto.php';
assertCli();

/**
 * @file
 * Generates a schema cache file from the contents of
 * library/HTMLPurifier/ConfigSchema/schema.ser
 */

$target = '../library/HTMLPurifier/ConfigSchema/schema.ser';
$FS = new FSTools();

if (file_exists($target)) {
    echo "Delete HTMLPurifier/ConfigSchema/schema.ser before running this script.";
    exit;
}

$files = $FS->globr('../library/HTMLPurifier/ConfigSchema', '*.txt');

$namespaces = array();
$directives = array();

// Generate string hashes
$parser = new ConfigSchema_StringHashParser();
foreach ($files as $file) {
    $hash = $parser->parseFile($file);
    if (strpos($hash['ID'], '.') === false) {
        $namespaces[] = $hash;
    } else {
        $directives[] = $hash;
    }
}

$adapter = new ConfigSchema_StringHashAdapter();
$schema  = new HTMLPurifier_ConfigSchema();

foreach ($namespaces as $hash) $adapter->adapt($hash, $schema);
foreach ($directives as $hash) $adapter->adapt($hash, $schema);

file_put_contents($target, serialize($schema));
