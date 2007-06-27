<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../library/');

require_once 'HTMLPurifier/ConfigSchema.php';
require_once 'HTMLPurifier/Config.php';
require_once 'HTMLPurifier/Lexer/DirectLex.php';
require_once 'HTMLPurifier/Context.php';

$input = file_get_contents('samples/Lexer/4.html');
$lexer = new HTMLPurifier_Lexer_DirectLex();
$config = HTMLPurifier_Config::createDefault();
$context = new HTMLPurifier_Context();

for ($i = 0; $i < 10; $i++) {
    $tokens = $lexer->tokenizeHTML($input, $config, $context);
}
