<?php

/** @file
 * Multiple PHP Versions test
 * 
 * This file tests HTML Purifier in all versions of PHP. It requires a
 * script called phpv that takes an extra argument, $version, before
 * the filename, is required. Contact me if you'd like to set up a
 * similar script.
 */

$versions_to_test = array(
    'FLUSH',
    '5.0.0',
    '5.0.1',
    '5.0.2',
    '5.0.3',
    '5.0.4',
    '5.0.5',
    '5.1.0',
    '5.1.1',
    '5.1.2',
    '5.1.3',
    '5.1.4',
    // '5.1.5', // zip appears to be missing
    '5.1.6',
    '5.2.0',
    '5.2.1',
    '5.2.2',
    '5.2.3',
    '5.2.4',
    '5.2.5',
    '5.3.0-dev',
    // '6.0.0-dev',
);

echo str_repeat('-', 70) . "\n";
echo "HTML Purifier\n";
echo "Multiple PHP Versions Test\n\n";

passthru("php ../maintenance/merge-library.php");

foreach ($versions_to_test as $version) {
    if ($version === 'FLUSH') {
        shell_exec('php ../maintenance/flush-definition-cache.php');
        continue;
    }
    passthru("phpv $version index.php");
    passthru("phpv $version index.php standalone");
    echo "\n\n";
}
