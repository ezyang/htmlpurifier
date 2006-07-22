<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../library/');

require_once 'HTMLPurifier/Lexer/DirectLex.php';

$input = file_get_contents('samples/Lexer/4.html');
$lexer = new HTMLPurifier_Lexer_DirectLex();

for ($i = 0; $i < 10; $i++) {
    $tokens = $lexer->tokenizeHTML($input);
}

?>