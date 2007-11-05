<?php

$versions_to_test = array(
    '4.3.10',
    '4.3.11',
    '4.4.6',
    '4.4.7',
    '5.1.6',
    '5.2.3',
    '5.2.4',
    '5.2.5RC2-dev',
    '5.3.0-dev',
    // '6.0.0-dev',
);

echo str_repeat('-', 70) . "\n";
echo "HTML Purifier\n";
echo "Multiple PHP Versions Test\n\n";

foreach ($versions_to_test as $version) {
    passthru("phpv $version index.php");
    echo "\n\n";
}
