<?php

header('Content-type: text/html; charset=UTF-8');

require_once '../library/HTMLPurifier.auto.php';
error_reporting(E_ALL | E_STRICT);

function escapeHTML($string) {
    $string = HTMLPurifier_Encoder::cleanUTF8($string);
    $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    return $string;
}

?>
