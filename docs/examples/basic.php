<?php

// This file demonstrates basic usage of HTMLPurifier.

exit; // not to be called directly, it will fail fantastically!

set_include_path('/path/to/htmlpurifier/library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';

$purifier = new HTMLPurifier();
$html = '<b>Simple and short';

$pure_html = $purifier->purify($html);

?>