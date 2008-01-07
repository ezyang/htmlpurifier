<?php

function assertCli() {
    if (php_sapi_name() != 'cli' && !getenv('PHP_IS_CLI')) {
        echo 'Script cannot be called from web-browser (if you are calling via cli,
set environment variable PHP_IS_CLI to work around this).';
        exit;
    }
}

/**
 * Filesystem tools not provided by default; can recursively create, copy
 * and delete folders. Some template methods are provided for extensibility.
 * @note This class must be instantiated to be used, although it does
 *       not maintain state.
 */
class FSTools
{
    
    /**
     * Recursively creates a directory
     * @param string $folder Name of folder to create
     * @note Adapted from the PHP manual comment 76612
     */
    function mkdir($folder) {
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
     * Copy a file, or recursively copy a folder and its contents; modified
     * so that copied files, if PHP, have includes removed
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1-modified
     * @link        http://aidanlister.com/repos/v/function.copyr.php
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @return      bool     Returns TRUE on success, FALSE on failure
     */
    function copyr($source, $dest) {
        // Simple copy for a file
        if (is_file($source)) {
            return $this->copy($source, $dest);
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
            if (!$this->copyable($entry)) {
                continue;
            }
            // Deep copy directories
            if ($dest !== "$source/$entry") {
                $this->copyr("$source/$entry", "$dest/$entry");
            }
        }
        // Clean up
        $dir->close();
        return true;
    }
    
    /**
     * Stub for PHP's built-in copy function, can be used to overload
     * functionality
     */
    function copy($source, $dest) {
        return copy($source, $dest);
    }
    
    /**
     * Overloadable function that tests a filename for copyability. By
     * default, everything should be copied; you can restrict things to
     * ignore hidden files, unreadable files, etc.
     */
    function copyable($file) {
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
            $this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }
     
        // Clean up
        $dir->close();
        return rmdir($dirname);
    }
    
    
}


