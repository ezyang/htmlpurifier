<?php

header('Content-type: text/html; charset=UTF-8');

set_include_path('../library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';

function escapeHTML($string) {
    $string = HTMLPurifier_Lexer::cleanUTF8($string);
    $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    return $string;
}

?>