<?php

$versions_to_test = array(
    'FLUSH',
    '4.3.7',
    '4.3.8',
    '4.3.9',
    'FLUSH', // serialize's behavior changed to be non-backwards-compat
    '4.3.10',
    '4.3.11',
    '4.4.6',
    '4.4.7',
    '5.0.4',
    '5.0.5',
    // We don't care about later versions: use HTML Purifier 3+!!!
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
