#!/usr/bin/php
<?php

chdir(dirname(__FILE__));
require_once 'common.php';
assertCli();

/**
 * @file
 * Runs all generation/flush cache scripts to ensure that somewhat volatile
 * generated files are up-to-date.
 */

function e($cmd) {
    echo "\$ $cmd\n";
    passthru($cmd, $status);
    echo "\n";
    if ($status) exit($status);
}

e($argv[0] . ' generate-includes.php');
e($argv[0] . ' generate-schema-cache.php');
e($argv[0] . ' flush-definition-cache.php');
e($argv[0] . ' generate-standalone.php');
