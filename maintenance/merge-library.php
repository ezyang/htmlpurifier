#!/usr/bin/php
<?php

require_once 'common.php';
assertCli();

/**
 * Compiles all of HTML Purifier's library files into one big file
 * named HTMLPurifier.standalone.php. Operates recursively, and will
 * barf if there are conditional includes.
 * 
 * Details: also creates blank "include" files in the test/blank directory
 * in order to simulate require_once's inside the test files.
 */

/**
 * Global array that tracks already loaded includes
 */
$GLOBALS['loaded'] = array('HTMLPurifier.php' => true);

/**
 * @param $text Text to replace includes from
 */
function replace_includes($text) {
    return preg_replace_callback(
        "/require_once ['\"]([^'\"]+)['\"];/",
        'replace_includes_callback',
        $text
    );
}

/**
 * Removes leading PHP tags from included files. Assumes that there is
 * no trailing tag.
 */
function remove_php_tags($text) {
    return substr($text, 5);
}

/**
 * Creates an appropriate blank file, recursively generating directories
 * if necessary
 */
function create_blank($file) {
    $dir = dirname($file);
    $base = realpath('../tests/blanks/') . DIRECTORY_SEPARATOR ;
    if ($dir != '.') mkdir_deep($base . $dir);
    file_put_contents($base . $file, '');
}

/**
 * Recursively creates a directory
 * @note Adapted from the PHP manual comment 76612
 */
function mkdir_deep($folder) {
    $folders = preg_split("#[\\\\/]#", $folder);
    $base = '';
    for($i = 0, $c = count($folders); $i < $c; $i++) {
        if(empty($folders[$i])) {
            if (!$i) {
                // special case for root level
                $base .= DIRECTORY_SEPARATOR;
            }
            continue;
        }
        $base .= $folders[$i];
        if(!is_dir($base)){
            mkdir($base);
        }
        $base .= DIRECTORY_SEPARATOR;
    }
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/repos/v/function.copyr.php
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest) {
    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }
    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest);
    }
    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
        // Skip hidden files
        if ($entry[0] == '.') {
            continue;
        }
        // Deep copy directories
        if ($dest !== "$source/$entry") {
            copyr("$source/$entry", "$dest/$entry");
        }
    }
    // Clean up
    $dir->close();
    return true;
}

/**
 * Delete a file, or a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.3
 * @link        http://aidanlister.com/repos/v/function.rmdirr.php
 * @param       string   $dirname    Directory to delete
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function rmdirr($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }
 
    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return unlink($dirname);
    }
 
    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }
 
        // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }
 
    // Clean up
    $dir->close();
    return rmdir($dirname);
}

/**
 * Copies the contents of a directory to the standalone directory
 */
function make_dir_standalone($dir) {
    return copyr($dir, 'standalone/' . $dir);
}

function make_file_standalone($file) {
    mkdir_deep('standalone/' . dirname($file));
    return copy($file, 'standalone/' . $file);
}

/**
 * @param $matches preg_replace_callback matches array, where index 1
 *        is the filename to include
 */
function replace_includes_callback($matches) {
    $file = $matches[1];
    // PHP 5 only file
    if ($file == 'HTMLPurifier/Lexer/DOMLex.php') {
        return $matches[0];
    }
    if (isset($GLOBALS['loaded'][$file])) return '';
    $GLOBALS['loaded'][$file] = true;
    create_blank($file);
    return replace_includes(remove_php_tags(file_get_contents($file)));
}

chdir(dirname(__FILE__) . '/../library/');
create_blank('HTMLPurifier.php');

echo 'Creating full file...';
$contents = replace_includes(file_get_contents('HTMLPurifier.php'));
$contents = str_replace(
    "define('HTMLPURIFIER_PREFIX', dirname(__FILE__));",
    "define('HTMLPURIFIER_PREFIX', dirname(__FILE__) . '/standalone');
set_include_path(HTMLPURIFIER_PREFIX . PATH_SEPARATOR . get_include_path());",
    $contents
);
file_put_contents('HTMLPurifier.standalone.php', $contents);
echo ' done!' . PHP_EOL;

echo 'Creating standalone directory...';
rmdirr('standalone'); // ensure a clean copy
mkdir_deep('standalone/HTMLPurifier/DefinitionCache/Serializer');
make_dir_standalone('HTMLPurifier/EntityLookup');
make_dir_standalone('HTMLPurifier/Language');
make_file_standalone('HTMLPurifier/Printer/ConfigForm.js');
make_file_standalone('HTMLPurifier/Printer/ConfigForm.css');
make_dir_standalone('HTMLPurifier/URIScheme');
// PHP 5 only file
mkdir_deep('standalone/HTMLPurifier/Lexer');
make_file_standalone('HTMLPurifier/Lexer/DOMLex.php');
make_file_standalone('HTMLPurifier/TokenFactory.php');
echo ' done!' . PHP_EOL;

