<?php

load_simpletest(); // includes all relevant simpletest files

// emulates inserting a dir called HTMLPurifier into your class dir
set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

require_once 'XML/HTMLSax3.php'; // optional PEAR class

// enforce proper namespacing
require_once 'HTMLPurifier/HTMLPurifier.php';
require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/Token.php';
require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/Generator.php';

$test = new GroupTest('HTMLPurifier');

$test->addTestFile('HTMLPurifier.php');
$test->addTestFile('Lexer.php');
//$test->addTestFile('Token.php');
$test->addTestFile('Definition.php');
$test->addTestFile('Generator.php');

$test->run( new HtmlReporter() );

?>