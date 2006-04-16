<?php

// test our parser versus HTMLSax parser

set_time_limit(5);

// PEAR
require_once 'Benchmark/Timer.php';
require_once 'XML/HTMLSax3.php';
require_once 'Text/Password.php';

require_once '../MarkupFragment.php';
require_once '../HTML_Lexer.php';

?>
<html>
<head>
<title>Benchmark: HTML_Lexer versus HTMLSax</title>
</head>
<body>
<h1>Benchmark: HTML_Lexer versus HTMLSax</h1>
<?php


function do_benchmark($document) {
    $timer = new Benchmark_Timer();
    $timer->start();
    
    $lexer = new HTML_Lexer();
    $tokens = $lexer->tokenizeHTML($document);
    $timer->setMarker('HTML_Lexer');
    
    $lexer = new HTML_Lexer_Sax();
    $sax_tokens = $lexer->tokenizeHTML($document);
    $timer->setMarker('HTML_Lexer_Sax');
    
    $timer->stop();
    $timer->display();
}

// sample of html pages

$dir = 'samples/HTML_Lexer';
$dh  = opendir($dir);
while (false !== ($filename = readdir($dh))) {
    
    if (strpos($filename, '.html') !== strlen($filename) - 5) continue;
    $document = file_get_contents($dir . '/' . $filename);
    echo "<h2>File: $filename</h2>\n";
    do_benchmark($document);
    
}

// crashers

$snippets = array();
$snippets[] = '<a href="foo>';
$snippets[] = '<a "=>';

foreach ($snippets as $snippet) {
    echo '<h2>' . htmlentities($snippet) . '</h2>';
    do_benchmark($snippet);
}

// random input

$document = Text_Password::create(80, 'unpronounceable', 'qwerty <>="\'');
echo "<h2>Random input</h2>\n";
echo '<p style="font-family:monospace;">' . htmlentities($document) . '</p>';
do_benchmark($document);

?></body></html>