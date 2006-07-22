<?php

load_simpletest(); // includes all relevant simpletest files

// emulates inserting a dir called HTMLPurifier into your class dir
set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

$test = new GroupTest('HTMLPurifier');

$test->addTestFile('HTMLPurifier.php');
$test->addTestFile('Lexer.php');
//$test->addTestFile('Token.php');
$test->addTestFile('Definition.php');
$test->addTestFile('ChildDef.php');
$test->addTestFile('Generator.php');

$test->run( new HtmlReporter() );

?>