<?php

// call one file using /?f=FileTest.php , see $test_files array for
// valid values

define('HTMLPurifierTest', 1);
define('HTMLPURIFIER_SCHEMA_STRICT', true); // validate schemas

require_once 'common.php';

// clean out cache if necessary
if (isset($_GET['flush'])) shell_exec('php ../maintenance/flush-definition-cache.php');

// initialize and load HTML Purifier
// use ?standalone to load the alterative standalone stub
if (isset($_GET['standalone']) || (isset($argv[1]) && $argv[1] == 'standalone')) {
    set_include_path(realpath('blanks') . PATH_SEPARATOR . get_include_path());
    require_once '../library/HTMLPurifier.standalone.php';
} else {
    require_once '../library/HTMLPurifier.auto.php';
}
require_once 'HTMLPurifier/Harness.php';

// setup special DefinitionCacheFactory decorator
$factory =& HTMLPurifier_DefinitionCacheFactory::instance();
$factory->addDecorator('Memory'); // since we deal with a lot of config objects

// load tests
$test_files = array();
require 'test_files.php'; // populates $test_files array
sort($test_files); // for the SELECT
$GLOBALS['HTMLPurifierTest']['Files'] = $test_files; // for the reporter
$test_file_lookup = array_flip($test_files);

// determine test file
if (isset($_GET['f']) && isset($test_file_lookup[$_GET['f']])) {
    $GLOBALS['HTMLPurifierTest']['File'] = $_GET['f'];
} elseif (isset($argv[1]) && isset($test_file_lookup[$argv[1]])) {
    // command-line
    $GLOBALS['HTMLPurifierTest']['File'] = $argv[1];
} else {
    $GLOBALS['HTMLPurifierTest']['File'] = false;
}

// we can't use addTestFile because SimpleTest chokes on E_STRICT warnings
if ($test_file = $GLOBALS['HTMLPurifierTest']['File']) {
    
    $test = new GroupTest($test_file);
    require_once $test_file;
    $test->addTestClass(path2class($test_file));
    
} else {
    
    $test = new GroupTest('All HTML Purifier tests on PHP ' . PHP_VERSION);
    foreach ($test_files as $test_file) {
        require_once $test_file;
        $test->addTestClass(path2class($test_file));
    }
    
}

if (SimpleReporter::inCli()) $reporter = new TextReporter();
else $reporter = new HTMLPurifier_SimpleTest_Reporter('UTF-8');

$test->run($reporter);
