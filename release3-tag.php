<?php

// Tags releases

if (php_sapi_name() != 'cli') {
    echo 'Release script cannot be called from web-browser.';
    exit;
}

require 'svn.php';

$svn_info = my_svn_info('.');

$version = trim(file_get_contents('VERSION'));

$trunk_url  = $svn_info['Repository Root'] . '/htmlpurifier/branches/php4';
$trunk_tag_url  = $svn_info['Repository Root'] . '/htmlpurifier/tags/' . $version;

echo "Tagging php4 branch to tags/$version...";
passthru("svn copy --message \"Tag $version release.\" $trunk_url $trunk_tag_url");
