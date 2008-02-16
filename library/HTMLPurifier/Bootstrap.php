<?php

// constants are slow, so we use as few as possible
if (!defined('HTMLPURIFIER_PREFIX')) {
    define('HTMLPURIFIER_PREFIX', realpath(dirname(__FILE__) . '/..'));
}

// accomodations for versions earlier than 5.0.2
// borrowed from PHP_Compat, LGPL licensed, by Aidan Lister <aidan@php.net>
if (!defined('PHP_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        case 'WIN':
            define('PHP_EOL', "\r\n");
            break;
        case 'DAR':
            define('PHP_EOL', "\r");
            break;
        default:
            define('PHP_EOL', "\n");
    }
}

// :TODO: Might be slow
if (!class_exists('HTMLPurifier_Bootstrap', false)) {

/**
 * Bootstrap class that contains meta-functionality for HTML Purifier such as
 * the autoload function.
 *
 * @note
 *      This class may be used without any other files from HTML Purifier.
 */
class HTMLPurifier_Bootstrap
{
    
    /**
     * Autoload function for HTML Purifier
     * @param $class Class to load
     */
    public static function autoload($class) {
        $file = HTMLPurifier_Bootstrap::getPath($class);
        if (!$file) return false;
        require $file;
        return true;
    }
    
    /**
     * Returns the path for a specific class.
     */
    public static function getPath($class) {
        if (strncmp('HTMLPurifier', $class, 12) !== 0) return false;
        // Custom implementations
        if (strncmp('HTMLPurifier_Language_', $class, 22) === 0) {
          $code = str_replace('_', '-', substr($class, 22));
          return 'HTMLPurifier/Language/classes/' . $code . '.php';
        }
        // Standard implementation
        return str_replace('_', '/', $class) . '.php';
    }
    
}

}

