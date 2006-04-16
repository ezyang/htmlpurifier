<?php

load_simpletest(); // includes all relevant simpletest files

require_once 'XML/HTMLSax3.php'; // optional PEAR class

require_once 'HTML_Purifier.php';
require_once 'HTML_Lexer.php';
require_once 'MarkupFragment.php';
require_once 'PureHTMLDefinition.php';

$test = new GroupTest('HTML_Purifier');

chdir('tests/');
$test->addTestFile('HTML_Purifier.php');
$test->addTestFile('HTML_Lexer.php');
//$test->addTestFile('MarkupFragment.php');
$test->addTestFile('PureHTMLDefinition.php');
chdir('../');

$test->run(new HtmlReporter());

?>