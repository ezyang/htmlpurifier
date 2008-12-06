#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require_once 'common.php';
require_once '../library/HTMLPurifier.auto.php';
assertCli();

/**
 * @file
 * Generates a schema cache file, saving it to
 * library/HTMLPurifier/ConfigSchema/schema.ser.
 *
 * This should be run when new configuration options are added to
 * HTML Purifier. A cached version is available via SVN so this does not
 * normally have to be regenerated.
 */

$target = '../library/HTMLPurifier/ConfigSchema/schema.ser';

$interchange = HTMLPurifier_ConfigSchema_InterchangeBuilder::buildFromDirectory();
$interchange->validate();

$schema_builder = new HTMLPurifier_ConfigSchema_Builder_ConfigSchema();
$schema = $schema_builder->build($interchange);

echo "Saving schema... ";
file_put_contents($target, serialize($schema));
echo "done!\n";

// vim: et sw=4 sts=4
