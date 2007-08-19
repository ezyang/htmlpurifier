<?php

header('Content-type: text/html; charset=UTF-8');

if (!isset($_GET['standalone'])) {
    require_once '../library/HTMLPurifier.auto.php';
} else {
    require_once '../library/HTMLPurifier.standalone.php';
}
error_reporting(E_ALL);

function escapeHTML($string) {
    $string = HTMLPurifier_Encoder::cleanUTF8($string);
    $string = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    return $string;
}

