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
$php = 'php'; // for safety

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
$AC['php'] = $php;
$AC['disable-phpt'] = false;
$AC['only-phpt'] = false;
$aliases = array(
    'f' => 'file',
    'q' => 'quiet',
);
htmlpurifier_parse_args($AC, $aliases);

if ($AC['xml']) {
    $reporter = new XmlReporter();
} else {
    $reporter = new TextReporter();
}

// Regenerate any necessary files
htmlpurifier_flush($AC['php'], $reporter);

$file = '';

$test_files = array();
require 'test_files.php';
if ($AC['file']) {
    $test_files_lookup = array_flip($test_files);
    if (isset($test_files_lookup[$AC['file']])) {
        $file = '--file=' . $AC['file'];
    } else {
        throw new Exception("Invalid file passed");
    }
}
// This allows us to get out of having to do dry runs.
$size = count($test_files);

// Setup the test
$test = new TestSuite('HTML Purifier Multiple Versions Test');
foreach ($versions_to_test as $version) {
    $flush = '';
    if (is_array($version)) {
        $version = $version[0];
        $flush = '--flush';
    }
    if (!$AC['only-phpt']) {
        if (!$AC['exclude-normal']) {
            $test->add(
                new CliTestCase(
                    "$phpv $version index.php --xml $flush --disable-phpt $file",
                    $AC['quiet'], $size
                )
            );
        }
        if (!$AC['exclude-standalone']) {
            $test->add(
                new CliTestCase(
                    "$phpv $version index.php --xml $flush --standalone --disable-phpt $file",
                    $AC['quiet'], $size
                )
            );
        }
    }
    if (!$AC['disable-phpt']) { // naming is not consistent
        $test->add(
            new CliTestCase(
                $AC['php'] . " index.php --xml --php \"$phpv $version\" --only-phpt",
                $AC['quiet'], $size
            )
        );
    }
}

// This is the HTML Purifier website's test XML file. We could
// add more websites, i.e. more configurations to test.
// $test->add(new RemoteTestCase('http://htmlpurifier.org/dev/tests/?xml=1', 'http://htmlpurifier.org/dev/tests/?xml=1&dry=1&flush=1'));

$test->run($reporter);
