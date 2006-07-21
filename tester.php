<?php

load_simpletest(); // includes all relevant simpletest files

require_once 'XML/HTMLSax3.php'; // optional PEAR class

require_once 'HTML_Purifier.php';
require_once 'Lexer.php';
require_once 'Token.php';
require_once 'PureHTMLDefinition.php';
require_once 'Generator.php';

$test = new GroupTest('HTML_Purifier');

chdir('tests/');
$test->addTestFile('HTML_Purifier.php');
$test->addTestFile('Lexer.php');
//$test->addTestFile('Token.php');
$test->addTestFile('PureHTMLDefinition.php');
$test->addTestFile('Generator.php');
chdir('../');

$test->run( new HtmlReporter() );

?>