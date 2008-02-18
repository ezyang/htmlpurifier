<?php

/**
 * @file
 * Convenience file that registers autoload handler for HTML Purifier.
 */

if (function_exists('spl_autoload_register')) {
    HTMLPurifier_Bootstrap::registerAutoload();
    if (function_exists('__autoload')) {
        // Be polite and ensure that userland autoload gets retained
        spl_autoload_register('__autoload');
    }
} elseif (!function_exists('__autoload')) {
    function __autoload($class) {
        return HTMLPurifier_Bootstrap::autoload($class);
    }
}
