<?php

if (!defined('HTMLPurifierTest')) {
    echo "Invalid entry point\n";
    exit;
}

// default settings (protect against register_globals)
$GLOBALS['HTMLPurifierTest'] = array();
$GLOBALS['HTMLPurifierTest']['PEAR'] = false; // do PEAR tests
$GLOBALS['HTMLPurifierTest']['PH5P'] = class_exists('DOMDocument');

// default library settings
$simpletest_location = 'simpletest/'; // reasonable guess
$csstidy_location = false;
$versions_to_test = array();
$phpv = 'phpv';

// load configuration
if (file_exists('../conf/test-settings.php')) include '../conf/test-settings.php';
if (file_exists('../test-settings.php')) include '../test-settings.php';

// load SimpleTest
require_once $simpletest_location . 'unit_tester.php';
require_once $simpletest_location . 'reporter.php';
require_once $simpletest_location . 'mock_objects.php';
require_once $simpletest_location . 'xml.php';

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
require_once 'CliTestCase.php';
require_once 'Debugger.php';
require_once 'generate_mock_once.func.php';
require_once 'path2class.func.php';
require_once 'tally_errors.func.php'; // compat

/**
 * Arguments parser, is cli and web agnostic.
 * @warning
 *   There are some quirks about the argument format:
 *     - Short flags cannot be chained together
 *     - Any number of hyphens are allowed to lead flags
 *     - Flag values cannot have spaces in them
 *     - You must specify an equal sign, --foo=value; --foo value doesn't work
 *     - Only strings and booleans are accepted
 *     - This --flag=off will be interpreted as true, use --flag=0 instead
 * @param $AC
 *   Arguments array to populate. This takes a simple format of 'argument'
 *   => default value. Depending on the type of the default value, 
 *   arguments will be typecast accordingly. For example, if
 *   'flag' => false is passed, all arguments for that will be cast to
 *   boolean. Do *not* pass null, as it will not be recognized.
 * @param $aliases
 *   
 */
function htmlpurifier_parse_args(&$AC, $aliases) {
    if (empty($_GET)) {
        array_shift($_SERVER['argv']);
        foreach ($_SERVER['argv'] as $opt) {
            if (strpos($opt, "=") !== false) {
                list($o, $v) = explode("=", $opt, 2);
            } else {
                $o = $opt;
                $v = true;
            }
            $o = ltrim($o, '-');
            htmlpurifier_args($AC, $aliases, $o, $v);
        }
    } else {
        foreach ($_GET as $o => $v) {
            if (get_magic_quotes_gpc()) $v = stripslashes($v);
            htmlpurifier_args($AC, $aliases, $o, $v);
        }
    }
}

/**
 * Actually performs assignment to $AC, see htmlpurifier_parse_args()
 * @param $AC Arguments array to write to
 * @param $aliases Aliases for options
 * @param $o Argument name
 * @param $v Argument value
 */
function htmlpurifier_args(&$AC, $aliases, $o, $v) {
    if (isset($aliases[$o])) $o = $aliases[$o];
    if (!isset($AC[$o])) return;
    if (is_string($AC[$o])) $AC[$o] = $v;
    if (is_bool($AC[$o])) $AC[$o] = true;
}
