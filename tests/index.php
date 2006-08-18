<?php

error_reporting(E_ALL);

// wishlist: automated calling of this file from multiple PHP versions so we
// don't have to constantly switch around

// configuration
$GLOBALS['HTMLPurifierTest']['PEAR'] = false; // do PEAR tests

$simpletest_location = 'simpletest/';
if (file_exists('../config.php')) include_once '../config.php';
require_once $simpletest_location . 'unit_tester.php';
require_once $simpletest_location . 'reporter.php';
require_once $simpletest_location . 'mock_objects.php';

// configure PEAR if necessary
if ( is_string($GLOBALS['HTMLPurifierTest']['PEAR']) ) {
    set_include_path($GLOBALS['HTMLPurifierTest']['PEAR'] . PATH_SEPARATOR .
        get_include_path());
}

// debugger
require_once 'Debugger.php';

// emulates inserting a dir called HTMLPurifier into your class dir
set_include_path('../library' . PATH_SEPARATOR . get_include_path());

// since Mocks can't be called from within test files, we need to do
// a little jumping through hoops to generate them
function generate_mock_once($name) {
    $mock_name = $name . 'Mock';
    if (class_exists($mock_name)) return false;
    Mock::generate($name, $mock_name);
}

// this has to be defined before we do any includes of library files
require_once 'HTMLPurifier/ConfigDef.php';

// define callable test files
$test_files = array();
$test_files[] = 'ConfigTest.php';
$test_files[] = 'ConfigDefTest.php';
$test_files[] = 'LexerTest.php';
$test_files[] = 'Lexer/DirectLexTest.php';
$test_files[] = 'TokenTest.php';
$test_files[] = 'ChildDefTest.php';
$test_files[] = 'GeneratorTest.php';
$test_files[] = 'EntityLookupTest.php';
$test_files[] = 'Strategy/RemoveForeignElementsTest.php';
$test_files[] = 'Strategy/MakeWellFormedTest.php';
$test_files[] = 'Strategy/FixNestingTest.php';
$test_files[] = 'Strategy/CompositeTest.php';
$test_files[] = 'Strategy/CoreTest.php';
$test_files[] = 'Strategy/ValidateAttributesTest.php';
$test_files[] = 'AttrDefTest.php';
$test_files[] = 'AttrDef/EnumTest.php';
$test_files[] = 'AttrDef/IDTest.php';
$test_files[] = 'AttrDef/ClassTest.php';
$test_files[] = 'AttrDef/TextTest.php';
$test_files[] = 'AttrDef/LangTest.php';
$test_files[] = 'AttrDef/PixelsTest.php';
$test_files[] = 'AttrDef/LengthTest.php';
$test_files[] = 'AttrDef/NumberSpanTest.php';
$test_files[] = 'AttrDef/URITest.php';
$test_files[] = 'AttrDef/CSSTest.php';
$test_files[] = 'AttrDef/CompositeTest.php';
$test_files[] = 'AttrDef/ColorTest.php';
$test_files[] = 'AttrDef/IntegerTest.php';
$test_files[] = 'AttrDef/NumberTest.php';
$test_files[] = 'AttrDef/CSSLengthTest.php';
$test_files[] = 'AttrDef/PercentageTest.php';
$test_files[] = 'AttrDef/MultipleTest.php';
$test_files[] = 'AttrDef/TextDecorationTest.php';
$test_files[] = 'AttrDef/FontFamilyTest.php';
$test_files[] = 'AttrDef/HostTest.php';
$test_files[] = 'AttrDef/IPv4Test.php';
$test_files[] = 'AttrDef/IPv6Test.php';
$test_files[] = 'IDAccumulatorTest.php';
$test_files[] = 'TagTransformTest.php';
$test_files[] = 'AttrTransform/LangTest.php';
$test_files[] = 'AttrTransform/TextAlignTest.php';
$test_files[] = 'AttrTransform/BdoDirTest.php';
$test_files[] = 'AttrTransform/ImgRequiredTest.php';
$test_files[] = 'URISchemeRegistryTest.php';
$test_files[] = 'URISchemeTest.php';

if (version_compare(PHP_VERSION, '5', '>=')) {
    $test_files[] = 'TokenFactoryTest.php';
}

$test_file_lookup = array_flip($test_files);

function htmlpurifier_path2class($path) {
    $temp = $path;
    $temp = str_replace('./', '',  $temp); // remove leading './'
    $temp = str_replace('.\\', '',  $temp); // remove leading '.\'
    $temp = str_replace('\\', '_', $temp); // normalize \ to _
    $temp = str_replace('/',  '_', $temp); // normalize / to _
    while(strpos($temp, '__') !== false) $temp = str_replace('__', '_', $temp);
    $temp = str_replace('.php', '', $temp);
    return $temp;
}

// we can't use addTestFile because SimpleTest chokes on E_STRICT warnings

if (isset($_GET['file']) && isset($test_file_lookup[$_GET['file']])) {
    
    // execute only one test
    $test_file = $_GET['file'];
    
    $test = new GroupTest('HTMLPurifier - ' . $test_file);
    $path = 'HTMLPurifier/' . $test_file;
    require_once $path;
    $test->addTestClass(htmlpurifier_path2class($path));
    
} else {
    
    $test = new GroupTest('HTMLPurifier');

    foreach ($test_files as $test_file) {
        $path = 'HTMLPurifier/' . $test_file;
        require_once $path;
        $test->addTestClass(htmlpurifier_path2class($path));
    }
    
}

if (SimpleReporter::inCli()) $reporter = new TextReporter();
else $reporter = new HTMLReporter('UTF-8');

$test->run($reporter);

?>
