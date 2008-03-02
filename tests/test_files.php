<?php

if (!defined('HTMLPurifierTest')) exit;

// define callable test files (sorted alphabetically)

if (!$AC['only-phpt']) {
    
    // HTML Purifier main library
    $test_dirs[] = 'HTMLPurifier';
    $test_files[] = 'HTMLPurifierTest.php';
    
    $test_dirs_exclude['HTMLPurifier/Filter/ExtractStyleBlocksTest.php'] = true;
    if ($csstidy_location) {
      $test_files[] = 'HTMLPurifier/Filter/ExtractStyleBlocksTest.php';
    }
    
    // ConfigDoc auxiliary library
    if (version_compare(PHP_VERSION, '5.2', '>=')) {
        $test_dirs[] = 'ConfigDoc';
    }
    
    // FSTools auxiliary library
    $test_dirs[] = 'FSTools';
    
}

// PHPT tests
if (!$AC['disable-phpt']) {
    $phpt_dirs = array();
    $phpt_dirs[] = 'HTMLPurifier/PHPT';
}
