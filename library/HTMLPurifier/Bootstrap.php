<?php

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
