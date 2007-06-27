<?php

// Merges in changes from trunk to strict branch
// WORKING COPY MUST BE POINTED TO STRICT BRANCH

if (php_sapi_name() != 'cli') {
    echo 'Release script cannot be called from web-browser.';
    exit;
}

require 'svn.php';

$svn_info = svn_info('.');

$last_rev = (int) $svn_info['Last Changed Rev'];
$trunk_url = $svn_info['Repository Root'] . '/htmlpurifier/trunk';
echo "Last revision was $last_rev, merging from $last_rev to head.\n";

$merge_cmd = "svn merge -r $last_rev:HEAD $trunk_url .";
$out = explode("\n", shell_exec($merge_cmd));

echo "Conflicted files:\n";
foreach ($out as $line) {
    if (empty($line)) continue;
    if ($line{0} === 'C' || $line{1} === 'C') echo $line . "\n";
}

$version = trim(file_get_contents('VERSION'));
echo "Resolve conflicts and then commit as 'Release $version, merged in $last_rev to HEAD.'";

