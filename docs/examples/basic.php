<?php

// This file demonstrates basic usage of HTMLPurifier.

// replace this with the path to the HTML Purifier library
require_once '../../library/HTMLPurifier.auto.php';

$config = HTMLPurifier_Config::createDefault();

// configuration goes here:
$config->set('Core', 'Encoding', 'ISO-8859-1'); //replace with your encoding
$config->set('Core', 'XHTML', true); // set to false if HTML 4.01

$purifier = new HTMLPurifier($config);

// untrusted input HTML
$html = '<b>Simple and short';

$pure_html = $purifier->purify($html);

echo '<pre>' . htmlspecialchars($pure_html) . '</pre>';

?>