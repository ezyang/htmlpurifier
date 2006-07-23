<?php

error_reporting(E_ALL);

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';

require_once 'Debugger.php';

// emulates inserting a dir called HTMLPurifier into your class dir
set_include_path(get_include_path() . PATH_SEPARATOR . '../library');

$test = new GroupTest('HTMLPurifier');

$test->addTestFile('HTMLPurifier/LexerTest.php');
$test->addTestFile('HTMLPurifier/Lexer/DirectLexTest.php');
//$test->addTestFile('TokenTest.php');
$test->addTestFile('HTMLPurifier/DefinitionTest.php');
$test->addTestFile('HTMLPurifier/ChildDefTest.php');
$test->addTestFile('HTMLPurifier/GeneratorTest.php');
$test->addTestFile('HTMLPurifier/EntityLookupTest.php');

$test->run( new HtmlReporter() );

?>