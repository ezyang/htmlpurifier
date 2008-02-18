<?php

/** @file
 * Multiple PHP Versions test
 * 
 * This file tests HTML Purifier in all versions of PHP. Arguments
 * are specified like --arg=opt, allowed arguments are:
 *   - exclude-normal, excludes normal tests
 *   - exclude-standalone, excludes standalone tests
 *   - file (f), specifies a single file to test for all versions
 *   - xml, if specified output is XML
 *   - quiet (q), if specified no informative messages are enabled (please use
 *     this if you're outputting XML)
 * 
 * @note
 *   It requires a script called phpv that takes an extra argument (the
 *   version number of PHP) before all other arguments. Contact me if you'd
 *   like to set up a similar script. The name of the script can be
 *   edited with $phpv
 * 
 * @note
 *   Also, configuration must be set up with a variable called 
 *   $versions_to_test specifying version numbers to pass to $phpv
 */

define('HTMLPurifierTest', 1);
require_once 'common.php';

if (!SimpleReporter::inCli()) {
    echo 'Multitest only available from command line';
    exit;
}

$AC = array(); // parameters
$AC['exclude-normal'] = false;
$AC['exclude-standalone'] = false;
$AC['file']  = '';
$AC['xml']   = false;
$AC['quiet'] = false;
$AC['php'] = 'php';
$AC['disable-phpt'] = false;
$AC['only-phpt'] = false;
$aliases = array(
    'f' => 'file',
    'q' => 'quiet',
);
htmlpurifier_parse_args($AC, $aliases);

// Calls generate-includes.php automatically
shell_exec($AC['php'] . ' ../maintenance/generate-standalone.php');

// Not strictly necessary, but its a good idea
shell_exec($AC['php'] . ' ../maintenance/generate-schema-cache.php');
shell_exec($AC['php'] . ' ../maintenance/flush-definition-cache.php');

$test = new TestSuite('HTML Purifier Multiple Versions Test');
$file = '';

$test_files = array();
require 'test_files.php';
if ($AC['file']) {
    $test_files_lookup = array_flip($test_files);
    if (isset($test_files_lookup[$AC['file']])) {
        $file = '--file=' . $AC['file'];
    } else {
        echo "Invalid file passed\n";
        exit;
    }
}
// This allows us to get out of having to do dry runs.
$size = count($test_files);

foreach ($versions_to_test as $version) {
    $flush = '';
    if (is_array($version)) {
        $version = $version[0];
        $flush = '--flush';
    }
    if (!$AC['only-phpt']) {
        if (!$AC['exclude-normal']) {
            $test->addTestCase(
                new CliTestCase(
                    "$phpv $version index.php --xml $flush --disable-phpt $file",
                    $AC['quiet'], $size
                )
            );
        }
        if (!$AC['exclude-standalone']) {
            $test->addTestCase(
                new CliTestCase(
                    "$phpv $version index.php --xml $flush --standalone --disable-phpt $file",
                    $AC['quiet'], $size
                )
            );
        }
    }
    if (!$AC['disable-phpt']) { // naming is not consistent
        $test->addTestCase(
            new CliTestCase(
                $AC['php'] . " index.php --xml --php \"$phpv $version\" --only-phpt",
                $AC['quiet'], $size
            )
        );
    }
}

// This is the HTML Purifier website's test XML file. We could
// add more websites, i.e. more configurations to test.
$test->addTestCase(new RemoteTestCase('http://htmlpurifier.org/dev/tests/?xml=1', 'http://htmlpurifier.org/dev/tests/?xml=1&dry=1&flush=1'));

if ($AC['xml']) {
    $reporter = new XmlReporter();
} else {
    $reporter = new TextReporter();
}
$test->run($reporter);

shell_exec($AC['php'] . ' ../maintenance/flush-definition-cache.php');
