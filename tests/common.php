<?php

if (!defined('HTMLPurifierTest')) {
    echo "Invalid entry point\n";
    exit;
}

// setup our own autoload, checking for HTMLPurifier library if spl_autoload_register
// is not allowed
function __autoload($class) {
    if (!function_exists('spl_autoload_register')) {
        if (HTMLPurifier_Bootstrap::autoload($class)) return true;
        if (HTMLPurifierExtras::autoload($class)) return true;
    }
    require_once str_replace('_', '/', $class) . '.php';
    return true;
}
if (function_exists('spl_autoload_register')) {
    spl_autoload_register('__autoload');
}

// default settings (protect against register_globals)
$GLOBALS['HTMLPurifierTest'] = array();
$GLOBALS['HTMLPurifierTest']['PEAR'] = false; // do PEAR tests
$GLOBALS['HTMLPurifierTest']['PH5P'] = class_exists('DOMDocument');

// default library settings
$simpletest_location = 'simpletest/'; // reasonable guess
$csstidy_location = false;
$versions_to_test = array();
$php  = 'php';
$phpv = 'phpv';

// load configuration
if (file_exists('../conf/test-settings.php')) include '../conf/test-settings.php';
if (file_exists('../test-settings.php')) include '../test-settings.php';

// load SimpleTest
require_once $simpletest_location . 'unit_tester.php';
require_once $simpletest_location . 'reporter.php';
require_once $simpletest_location . 'mock_objects.php';
require_once $simpletest_location . 'xml.php';
require_once $simpletest_location . 'remote.php';

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

// initialize HTML Purifier
require_once '../library/HTMLPurifier.auto.php';

// initialize alternative classes
require_once '../extras/HTMLPurifierExtras.auto.php';

// load SimpleTest addon functions
require_once 'generate_mock_once.func.php';
require_once 'path2class.func.php';
require_once 'tally_errors.func.php'; // compat

/**
 * Arguments parser, is cli and web agnostic.
 * @warning
 *   There are some quirks about the argument format:
 *     - Short boolean flags cannot be chained together
 *     - Only strings, integers and booleans are accepted
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
        $o = false;
        $bool = false;
        $val_is_bool = false;
        foreach ($_SERVER['argv'] as $opt) {
            if ($o !== false) {
                $v = $opt;
            } else {
                if ($opt === '') continue;
                if (strlen($opt) > 2 && strncmp($opt, '--', 2) === 0) {
                    $o = substr($opt, 2);
                } elseif ($opt[0] == '-') {
                    $o = substr($opt, 1);
                } else {
                    $lopt = strtolower($opt);
                    if ($bool !== false && ($opt === '0' || $lopt === 'off' || $lopt === 'no')) {
                        $o = $bool;
                        $v = false;
                        $val_is_bool = true;
                    } elseif (isset($aliases[''])) {
                        $o = $aliases[''];
                    }
                }
                $bool = false;
                if (!isset($AC[$o]) || !is_bool($AC[$o])) {
                    if (strpos($o, '=') === false) {
                        continue;
                    }
                    list($o, $v) = explode('=', $o);
                } elseif (!$val_is_bool) {
                    $v = true;
                    $bool = $o;
                }
                $val_is_bool = false;
            }
            if ($o === false) continue;
            htmlpurifier_args($AC, $aliases, $o, $v);
            $o = false;
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
    if (is_bool($AC[$o]))   $AC[$o] = (bool) $v;
    if (is_int($AC[$o]))    $AC[$o] = (int) $v;
}

/**
 * Adds a test-class; depending on the file's extension this may involve
 * a regular UnitTestCase or a special PHPT test
 */
function htmlpurifier_add_test($test, $test_file, $only_phpt = false) {
    switch (strrchr($test_file, ".")) {
        case '.phpt':
            return $test->addTestCase(new PHPT_Controller_SimpleTest($test_file));
        case '.php':
            require_once $test_file;
            return $test->addTestClass(path2class($test_file));
        default:
            trigger_error("$test_file is an invalid file for testing", E_USER_ERROR);
    }
}