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

$files = $FS->globr('../library/HTMLPurifier/ConfigSchema/schema', '*.txt');
if (!$files) throw new Exception('Did not find any schema files');

$parser      = new HTMLPurifier_StringHashParser();
$builder     = new HTMLPurifier_ConfigSchema_InterchangeBuilder();
$interchange = new HTMLPurifier_ConfigSchema_Interchange();
foreach ($files as $file) {
    $builder->build($interchange, new HTMLPurifier_StringHash($parser->parseFile($file)));
}

$validator = new HTMLPurifier_ConfigSchema_Validator();
$validator->validate($interchange);

$schema_builder = new HTMLPurifier_ConfigSchema_Builder_ConfigSchema();
$schema = $schema_builder->build($interchange);

echo "Saving schema... ";
file_put_contents($target, serialize($schema));
echo "done!\n";
