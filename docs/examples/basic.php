<?php exit;

// This file demonstrates basic usage of HTMLPurifier.

require_once '/path/to/htmlpurifier/library/HTMLPurifier.auto.php';

$purifier = new HTMLPurifier();
$html = '<b>Simple and short';

$pure_html = $purifier->purify($html);

echo $pure_html;

?>