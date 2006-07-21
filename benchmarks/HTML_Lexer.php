<?php

// test our parser versus HTMLSax parser

set_time_limit(5);

// PEAR
require_once 'Benchmark/Timer.php';
require_once 'XML/HTMLSax3.php';
require_once 'Text/Password.php';

require_once '../Token.php';
require_once '../Lexer.php';

class TinyTimer extends Benchmark_Timer
{
    
    var $name;
    
    function TinyTimer($name, $auto = false) {
        $this->name = htmlentities($name);
        $this->Benchmark_Timer($auto);
    }
    
    function getOutput() {

        $total  = $this->TimeElapsed();
        $result = $this->getProfiling();
        $dashes = '';
        
        $out = '<tr>';
        
        $out .= "<td>{$this->name}</td>";
        
        foreach ($result as $k => $v) {
            if ($v['name'] == 'Start' || $v['name'] == 'Stop') continue;
            
            $perc = (($v['diff'] * 100) / $total);
            $tperc = (($v['total'] * 100) / $total);
            
            $out .= '<td align="right">' . number_format($perc, 2, '.', '') .
                   "%</td>";
            
        }
        
        $out .= '</tr>';
        
        return $out;
    }
}

?>
<html>
<head>
<title>Benchmark: HTMLPurifier_Lexer versus HTMLSax</title>
</head>
<body>
<h1>Benchmark: HTMLPurifier_Lexer versus HTMLSax</h1>
<table border="1">
<tr><th>Case</th><th>HTMLPurifier_Lexer</th><th>HTMLPurifier_Lexer_Sax</th></tr>
<?php


function do_benchmark($name, $document) {
    $timer = new TinyTimer($name);
    $timer->start();
    
    $lexer = new HTMLPurifier_Lexer();
    $tokens = $lexer->tokenizeHTML($document);
    $timer->setMarker('HTMLPurifier_Lexer');
    
    $lexer = new HTMLPurifier_Lexer_Sax();
    $sax_tokens = $lexer->tokenizeHTML($document);
    $timer->setMarker('HTMLPurifier_Lexer_Sax');
    
    $timer->stop();
    $timer->display();
}

// sample of html pages

$dir = 'samples/Lexer';
$dh  = opendir($dir);
while (false !== ($filename = readdir($dh))) {
    
    if (strpos($filename, '.html') !== strlen($filename) - 5) continue;
    $document = file_get_contents($dir . '/' . $filename);
    do_benchmark("File: $filename", $document);
    
}

// crashers

$snippets = array();
$snippets[] = '<a href="foo>';
$snippets[] = '<a "=>';

foreach ($snippets as $snippet) {
    do_benchmark($snippet, $snippet);
}

// random input

$random = Text_Password::create(80, 'unpronounceable', 'qwerty <>="\'');

do_benchmark('Random input', $random);

?></table>

<?php

echo '<div>Random input was: ' .
  '<span colspan="4" style="font-family:monospace;">' . htmlentities($random) .
  '</span></div>';

?>


</body></html>