<?php

// Tags releases

if (php_sapi_name() != 'cli') {
    echo 'Release script cannot be called from web-browser.';
    exit;
}

require 'svn.php';

$svn_info = svn_info('.');

$version = trim(file_get_contents('VERSION'));

$trunk_url  = $svn_info['Repository Root'] . '/htmlpurifier/trunk';
$strict_url = $svn_info['Repository Root'] . '/htmlpurifier/branches/strict';
$trunk_tag_url  = $svn_info['Repository Root'] . '/htmlpurifier/tags/' . $version;
$strict_tag_url = $svn_info['Repository Root'] . '/htmlpurifier/tags/' . $version . '-strict';

echo "Tagging trunk to tags/$version...";
passthru("svn copy --message \"Tag $version release.\" $trunk_url $trunk_tag_url");
echo "Tagging strict to tags/$version-strict...";
passthru("svn copy --message \"Tag $version-strict release.\" $strict_url $strict_tag_url");

