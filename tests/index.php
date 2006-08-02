<?php

error_reporting(E_ALL);

// wishlist: automated calling of this file from multiple PHP versions so we
// don't have to constantly switch around

// load files, assume that simpletest directory is in path
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Debugger.php';

// emulates inserting a dir called HTMLPurifier into your class dir
set_include_path(get_include_path() . PATH_SEPARATOR . '../library');

// since Mocks can't be called from within test files, we need to do
// a little jumping through hoops to generate them
function generate_mock_once($name) {
    $mock_name = $name . 'Mock';
    if (class_exists($mock_name)) return false;
    Mock::generate($name, $mock_name);
}

$test = new GroupTest('HTMLPurifier');

$test->addTestFile('HTMLPurifier/LexerTest.php');
$test->addTestFile('HTMLPurifier/Lexer/DirectLexTest.php');
//$test->addTestFile('TokenTest.php');
$test->addTestFile('HTMLPurifier/ChildDefTest.php');
$test->addTestFile('HTMLPurifier/GeneratorTest.php');
$test->addTestFile('HTMLPurifier/EntityLookupTest.php');
$test->addTestFile('HTMLPurifier/Strategy/RemoveForeignElementsTest.php');
$test->addTestFile('HTMLPurifier/Strategy/MakeWellFormedTest.php');
$test->addTestFile('HTMLPurifier/Strategy/FixNestingTest.php');
$test->addTestFile('HTMLPurifier/Strategy/CompositeTest.php');
$test->addTestFile('HTMLPurifier/Strategy/CoreTest.php');
$test->addTestFile('HTMLPurifier/Strategy/ValidateAttributesTest.php');
$test->addTestFile('HTMLPurifier/AttrDef/EnumTest.php');
$test->addTestFile('HTMLPurifier/AttrDef/IDTest.php');
$test->addTestFile('HTMLPurifier/IDAccumulatorTest.php');
$test->addTestFile('HTMLPurifier/TagTransformTest.php');

if (SimpleReporter::inCli()) $reporter = new TextReporter();
else $reporter = new HTMLReporter();

$test->run($reporter);

?>