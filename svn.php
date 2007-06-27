<?php

function svn_info($dir) {
    $raw = explode("\n", shell_exec("svn info $dir"));
    $svn_info = array();
    foreach ($raw as $r) {
        if (empty($r)) continue;
        list($k, $v) = explode(': ', $r, 2);
        $svn_info[$k] = $v;
    }
    return $svn_info;
}

