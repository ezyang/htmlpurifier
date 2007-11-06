<?php

$versions_to_test = array(
    'FLUSH',
    '5.0.4',
    '5.0.5',
    '5.1.4',
    '5.1.6',
    '5.2.0',
    '5.2.1',
    '5.2.2',
    '5.2.3',
    '5.2.4',
    '5.2.5RC2-dev',
    '5.3.0-dev',
    // '6.0.0-dev',
);

echo str_repeat('-', 70) . "\n";
echo "HTML Purifier\n";
echo "Multiple PHP Versions Test\n\n";

passthru("php ../maintenance/merge-library.php");

foreach ($versions_to_test as $version) {
    if ($version === 'FLUSH') {
        shell_exec('php ../maintenance/flush-definition-cache.php');
        continue;
    }
    passthru("phpv $version index.php");
    passthru("phpv $version index.php standalone");
    echo "\n\n";
}
