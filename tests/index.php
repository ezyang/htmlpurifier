<?php

load_simpletest(); // includes all relevant simpletest files

// emulates inserting a dir called HTMLPurifier into your class dir
set_include_path(get_include_path() . PATH_SEPARATOR . '../library');

$test = new GroupTest('HTMLPurifier');

$test->addTestFile('HTMLPurifier/LexerTest.php');
//$test->addTestFile('TokenTest.php');
$test->addTestFile('HTMLPurifier/DefinitionTest.php');
$test->addTestFile('HTMLPurifier/ChildDefTest.php');
$test->addTestFile('HTMLPurifier/GeneratorTest.php');

$test->run( new HtmlReporter() );

?>