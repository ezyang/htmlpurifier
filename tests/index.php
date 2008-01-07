<?php

/** @file
 * Unit tester
 * 
 * The heart and soul of HTML Purifier's correctness; anything and everything
 * is tested here! Arguments are specified like --arg=opt, allowed arguments
 * are:
 *   - flush, whether or not to flush definition caches before running
 *   - standalone, whether or not to test the standalone version
 *   - file (f), a single file to test
 *   - xml, whether or not to output XML
 */

define('HTMLPurifierTest', 1);
define('HTMLPURIFIER_SCHEMA_STRICT', true); // validate schemas

require_once 'common.php';

$AC = array(); // parameters
$AC['flush'] = false;
$AC['standalone'] = false;
$AC['file'] = '';
$AC['xml'] = false;
$aliases = array(
    'f' => 'file',
);
htmlpurifier_parse_args($AC, $aliases);

// clean out cache if necessary
if ($AC['flush']) shell_exec('php ../maintenance/flush-definition-cache.php');

// initialize and load HTML Purifier
// use ?standalone to load the alterative standalone stub
if ($AC['standalone']) {
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
if ($AC['file']) {
    if (!isset($test_file_lookup[$AC['file']])) {
        echo "Invalid file passed\n";
        exit;
    }
}

// we can't use addTestFile because SimpleTest chokes on E_STRICT warnings
if ($AC['file']) {
    
    $test = new TestSuite($AC['file']);
    require_once $AC['file'];
    $test->addTestClass(path2class($AC['file']));
    
} else {
    
    $standalone = '';
    if ($AC['standalone']) $standalone = ' (standalone)';
    $test = new TestSuite('All HTML Purifier tests on PHP ' . PHP_VERSION . $standalone);
    foreach ($test_files as $test_file) {
        require_once $test_file;
        $test->addTestClass(path2class($test_file));
    }
    
}

if ($AC['xml']) {
    if (!SimpleReporter::inCli()) header('Content-Type: text/xml;charset=UTF-8');
    $reporter = new XmlReporter();
} elseif (SimpleReporter::inCli()) {
    $reporter = new TextReporter();
} else {
    $reporter = new HTMLPurifier_SimpleTest_Reporter('UTF-8', $AC);
}

$test->run($reporter);
