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
 *   - dry, whether or not to do a dry run
 *
 * @warning File setup does not exactly match with autoloader; make sure that
 *          non-test classes (i.e. classes that are not retrieved using
 *          $test_files) do not have underscores in their names.
 */

define('HTMLPurifierTest', 1);
define('HTMLPURIFIER_SCHEMA_STRICT', true); // validate schemas
chdir(dirname(__FILE__));

require 'common.php';

$AC = array(); // parameters
$AC['flush'] = false;
$AC['standalone'] = false;
$AC['file'] = '';
$AC['xml'] = false;
$AC['dry'] = false;
$AC['php'] = 'php';

// Convenience parameters for running quicker tests; ideally all tests
// should be performed.
$AC['disable-phpt'] = false;
$AC['only-phpt'] = false;

$aliases = array(
    'f' => 'file',
);

// It's important that this does not call the autoloader. Not a problem
// with a function, but could be if we put this in a class.
htmlpurifier_parse_args($AC, $aliases);

// Disable PHPT tests if they're not enabled
if (!$GLOBALS['HTMLPurifierTest']['PHPT']) $AC['disable-phpt'] = true;

if (!SimpleReporter::inCli()) {
    // Undo any dangerous parameters
    $AC['php'] = $php;
}

if ($AC['disable-phpt'] && $AC['only-phpt']) {
    echo "Cannot disable and allow only PHPT tests!\n";
    exit(1);
}

// initialize and load HTML Purifier
// use ?standalone to load the alterative standalone stub
if ($AC['standalone']) {
    require '../library/HTMLPurifier.standalone.php';
} else {
    require '../library/HTMLPurifier.path.php';
    require 'HTMLPurifier.includes.php';
    require '../library/HTMLPurifier.autoload.php';
}
require 'HTMLPurifier/Harness.php';

// Shell-script code is executed

if ($AC['flush']) {
    if (SimpleReporter::inCli() && !$AC['xml']) {
        passthru($AC['php'] . ' ../maintenance/flush.php');
    } else {
        shell_exec($AC['php'] . ' ../maintenance/flush.php');
    }
}

// Now, userland code begins to be executed

// setup special DefinitionCacheFactory decorator
$factory =& HTMLPurifier_DefinitionCacheFactory::instance();
$factory->addDecorator('Memory'); // since we deal with a lot of config objects

if (!$AC['disable-phpt']) {
    $phpt = PHPT_Registry::getInstance();
    $phpt->php = $AC['php'];
}

// load tests

$test_files = array();
$test_dirs = array();
$test_dirs_exclude = array();
$phpt_dirs  = array();

require 'test_files.php'; // populates $test_files array

$FS = new FSTools();

// handle test dirs
foreach ($test_dirs as $dir) {
    $raw_files = $FS->globr($dir, '*Test.php');
    foreach ($raw_files as $file) {
        $file = str_replace('\\', '/', $file);
        if (isset($test_dirs_exclude[$file])) continue; 
        $test_files[] = $file;
    }
}

// handle phpt files
foreach ($phpt_dirs as $dir) {
    $phpt_files = $FS->globr($dir, '*.phpt');
    foreach ($phpt_files as $file) {
        $test_files[] = str_replace('\\', '/', $file);
    }
}

array_unique($test_files);
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
    htmlpurifier_add_test($test, $AC['file']);
    
} else {
    
    $standalone = '';
    if ($AC['standalone']) $standalone = ' (standalone)';
    $test = new TestSuite('All HTML Purifier tests on PHP ' . PHP_VERSION . $standalone);
    foreach ($test_files as $test_file) {
        htmlpurifier_add_test($test, $test_file);
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

if ($AC['dry']) $reporter->makeDry();

$test->run($reporter);
