<?php

// call one file using /?f=FileTest.php , see $test_files array for
// valid values

error_reporting(E_ALL | E_STRICT);
define('HTMLPurifierTest', 1);
define('HTMLPURIFIER_SCHEMA_STRICT', true);

// wishlist: automated calling of this file from multiple PHP versions so we
// don't have to constantly switch around

// default settings (protect against register_globals)
$GLOBALS['HTMLPurifierTest'] = array();
$GLOBALS['HTMLPurifierTest']['PEAR'] = false; // do PEAR tests
$simpletest_location = 'simpletest/'; // reasonable guess

// load SimpleTest
@include '../test-settings.php'; // don't mind if it isn't there
require_once $simpletest_location . 'unit_tester.php';
require_once $simpletest_location . 'reporter.php';
require_once $simpletest_location . 'mock_objects.php';
require_once 'HTMLPurifier/SimpleTest/Reporter.php';

// load Debugger
require_once 'Debugger.php';

// load convenience functions
require_once 'generate_mock_once.func.php';
require_once 'path2class.func.php';
require_once 'tally_errors.func.php'; // compat

// initialize PEAR (optional)
if ( is_string($GLOBALS['HTMLPurifierTest']['PEAR']) ) {
    // if PEAR is true, we assume that there's no need to
    // add it to the path
    set_include_path($GLOBALS['HTMLPurifierTest']['PEAR'] . PATH_SEPARATOR .
        get_include_path());
}

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
    
    $test = new GroupTest('All Tests');

    foreach ($test_files as $test_file) {
        require_once $test_file;
        $test->addTestClass(path2class($test_file));
    }
    
}

if (SimpleReporter::inCli()) $reporter = new TextReporter();
else $reporter = new HTMLPurifier_SimpleTest_Reporter('UTF-8');

$test->run($reporter);


