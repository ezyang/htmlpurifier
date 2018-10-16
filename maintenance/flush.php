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

function e($php, $file)
{
    $whitlistFiles = array(
        'generate-includes.php',
        'generate-schema-cache.php',
        'flush-definition-cache.php',
        'generate-standalone.php',
        'config-scanner.php',
    );
    if (in_array($file, $whitlistFiles)) {
        $cmd = $php . ' ' . $file;
    } else {
        echo 'file ' . $file . ' is not existed';
        exit();
    }
    echo "\$ $cmd\n";
    passthru($cmd, $status);
    echo "\n";
    if ($status) exit($status);
}

$php = PHP_BINARY . '/php';

e($php, 'generate-includes.php');
e($php, 'generate-schema-cache.php');
e($php, 'flush-definition-cache.php');
e($php, 'generate-standalone.php');
e($php, 'config-scanner.php');

// vim: et sw=4 sts=4
