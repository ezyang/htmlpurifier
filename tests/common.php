<?php

if (!defined('HTMLPurifierTest')) exit;

// default settings (protect against register_globals)
$GLOBALS['HTMLPurifierTest'] = array();
$GLOBALS['HTMLPurifierTest']['PEAR'] = false; // do PEAR tests
$GLOBALS['HTMLPurifierTest']['PH5P'] = class_exists('DOMDocument');

// default library settings
$simpletest_location = 'simpletest/'; // reasonable guess
$csstidy_location = false;

// load configuration
if (file_exists('../conf/test-settings.php')) include '../conf/test-settings.php';
if (file_exists('../test-settings.php')) include '../test-settings.php';

// load SimpleTest
require_once $simpletest_location . 'unit_tester.php';
require_once $simpletest_location . 'reporter.php';
require_once $simpletest_location . 'mock_objects.php';

// load CSS Tidy
if ($csstidy_location !== false) {
    require_once $csstidy_location . 'class.csstidy.php';
    require_once $csstidy_location . 'class.csstidy_print.php';
}

// load PEAR to include path
if ( is_string($GLOBALS['HTMLPurifierTest']['PEAR']) ) {
    // if PEAR is true, there's no need to add it to the path
    set_include_path($GLOBALS['HTMLPurifierTest']['PEAR'] . PATH_SEPARATOR .
        get_include_path());
}

// after external libraries are loaded, turn on compile time errors
error_reporting(E_ALL | E_STRICT);

// load SimpleTest addons
require_once 'HTMLPurifier/SimpleTest/Reporter.php';
require_once 'Debugger.php';
require_once 'generate_mock_once.func.php';
require_once 'path2class.func.php';
require_once 'tally_errors.func.php'; // compat

