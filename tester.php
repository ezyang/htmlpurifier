<?php

load_simpletest(); // includes all relevant simpletest files

require_once 'XML/HTMLSax3.php'; // optional PEAR class

require_once 'HTML_Purifier.php';

$test = new GroupTest('HTML_Purifier');

chdir('tests/');
$test->addTestFile('HTML_Purifier.php');
chdir('../');

$test->run(new HtmlReporter());

?>