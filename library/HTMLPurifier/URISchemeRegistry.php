<?php

HTMLPurifier_ConfigDef::define(
    'URI', 'AllowedSchemes', array(
        'http'  => true, // "Hypertext Transfer Protocol", nuf' said
        'https' => true, // HTTP over SSL (Secure Socket Layer)
        // quite useful, but not necessary
        'mailto' => true,// Email
        'ftp'   => true, // "File Transfer Protocol"
        'irc'   => true, // "Internet Relay Chat", usually needs another app
        // for Usenet, these two are similar, but distinct
        'nntp'  => true, // individual Netnews articles
        'news'  => true  // newsgroup or individual Netnews articles),
    ),
    'Whitelist that defines the schemes that a URI is allowed to have.  This '.
    'prevents XSS attacks from using pseudo-schemes like javascript or mocha.'
);

HTMLPurifier_ConfigDef::define(
    'URI', 'OverrideAllowedSchemes', true,
    'If this is set to true (which it is by default), you can override '.
    '%URI.AllowedSchemes by simply registering a HTMLPurifier_URIScheme '.
    'to the registry.  If false, you will also have to update that directive '.
    'in order to add more schemes.'
);

class HTMLPurifier_URISchemeRegistry
{
    
    // pass a registry object $prototype with a compatible interface and
    // the function will copy it and return it all further times.
    // pass bool true to reset to the default registry
    function &instance($prototype = null) {
        static $instance = null;
        if ($prototype !== null) {
            $instance = $prototype;
        } elseif ($instance === null || $prototype == true) {
            $instance = new HTMLPurifier_URISchemeRegistry();
        }
        return $instance;
    }
    
    var $schemes = array();
    var $_scheme_dir = null;
    
    function &getScheme($scheme, $config = null) {
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        $null = null; // for the sake of passing by reference
        
        // important, otherwise attacker could include arbitrary file
        $allowed_schemes = $config->get('URI', 'AllowedSchemes');
        if (!$config->get('URI', 'OverrideAllowedSchemes') &&
            !isset($allowed_schemes[$scheme])
        ) {
            return $null;
        }
        
        if (isset($this->schemes[$scheme])) return $this->schemes[$scheme];
        if (empty($this->_dir)) $this->_dir = dirname(__FILE__) . '/URIScheme/';
        
        if (!isset($allowed_schemes[$scheme])) return $null;
        
        @include_once $this->_dir . $scheme . '.php';
        $class = 'HTMLPurifier_URIScheme_' . $scheme;
        if (!class_exists($class)) return $null;
        $this->schemes[$scheme] = new $class();
        return $this->schemes[$scheme];
    }
    
    function register($scheme, &$scheme_obj) {
        $this->schemes[$scheme] =& $scheme_obj;
    }
    
}

?>
