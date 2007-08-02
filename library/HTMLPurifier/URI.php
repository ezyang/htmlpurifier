<?php

require_once 'HTMLPurifier/URIParser.php';
require_once 'HTMLPurifier/URIFilter.php';

/**
 * HTML Purifier's internal representation of a URI
 */
class HTMLPurifier_URI
{
    
    var $scheme, $userinfo, $host, $port, $path, $query, $fragment;
    
    /**
     * @note Automatically normalizes scheme and port
     */
    function HTMLPurifier_URI($scheme, $userinfo, $host, $port, $path, $query, $fragment) {
        $this->scheme = is_null($scheme) || ctype_lower($scheme) ? $scheme : strtolower($scheme);
        $this->userinfo = $userinfo;
        $this->host = $host;
        $this->port = is_null($port) ? $port : (int) $port;
        $this->path = $path;
        $this->query = $query;
        $this->fragment = $fragment;
    }
    
    function getSchemeObj($config, &$context) {
        $registry =& HTMLPurifier_URISchemeRegistry::instance();
        if ($this->scheme !== null) {
            $scheme_obj = $registry->getScheme($this->scheme, $config, $context);
            if (!$scheme_obj) return false; // invalid scheme, clean it out
        } else {
            // no scheme: retrieve the default one
            $scheme_obj = $registry->getScheme($config->get('URI', 'DefaultScheme'), $config, $context);
            if (!$scheme_obj) {
                // something funky happened to the default scheme object
                trigger_error(
                    'Default scheme object "' . $config->get('URI', 'DefaultScheme') . '" was not readable',
                    E_USER_WARNING
                );
                return false;
            }
        }
        return $scheme_obj;
    }
    
    /**
     * Generic validation method applicable for all schemes
     */
    function validate($config, &$context) {
        
        // validate host
        if (!is_null($this->host)) {
            
            $host_def = new HTMLPurifier_AttrDef_URI_Host();
            $this->host = $host_def->validate($this->host, $config, $context);
            if ($this->host === false) $this->host = null;
            
            // check host against blacklist
            if ($this->checkBlacklist($this->host, $config, $context)) return false;
            
        }
        
        // munge scheme off if necessary
        if (!is_null($this->scheme) && is_null($this->host)) {
            if ($config->get('URI', 'DefaultScheme') == $this->scheme) {
                $this->scheme = null;
            }
        }
        
        // validate port
        if (!is_null($this->port)) {
            if ($this->port < 1 || $this->port > 65535) $this->port = null;
        }
        
        // query and fragment are quite simple in terms of definition:
        // *( pchar / "/" / "?" ), so define their validation routines
        // when we start fixing percent encoding
        
        // path gets to be validated against a hodge-podge of rules depending
        // on the status of authority and scheme, but it's not that important,
        // esp. since it won't be applicable to everyone
        
        return true;
        
    }
    
    /**
     * Checks a host against an array blacklist
     * @param $host Host to check
     * @param $config HTMLPurifier_Config instance
     * @param $context HTMLPurifier_Context instance
     * @return bool Is spam?
     */
    function checkBlacklist($host, $config, &$context) {
        $blacklist = $config->get('URI', 'HostBlacklist');
        if (!empty($blacklist)) {
            foreach($blacklist as $blacklisted_host_fragment) {
                if (strpos($host, $blacklisted_host_fragment) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Convert URI back to string
     */
    function toString() {
        // reconstruct authority
        $authority = null;
        if (!is_null($this->host)) {
            $authority = '';
            if(!is_null($this->userinfo)) $authority .= $this->userinfo . '@';
            $authority .= $this->host;
            if(!is_null($this->port))     $authority .= ':' . $this->port;
        }
        
        // reconstruct the result
        $result = '';
        if (!is_null($this->scheme))    $result .= $this->scheme . ':';
        if (!is_null($authority))       $result .=  '//' . $authority;
        $result .= $this->path;
        if (!is_null($this->query))     $result .= '?' . $this->query;
        if (!is_null($this->fragment))  $result .= '#' . $this->fragment;
        
        return $result;
    }
    
}

