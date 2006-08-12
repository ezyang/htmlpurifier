<?php

require_once 'HTMLPurifier/URIScheme.php';
require_once 'HTMLPurifier/URISchemeRegistry.php';

class HTMLPurifier_AttrDef_URI extends HTMLPurifier_AttrDef
{
    function validate($uri, $config = null) {
        
        // We'll write stack-based parsers later, for now, use regexps to
        // get things working as fast as possible (irony)
        
        // parse as CDATA
        $uri = $this->parseCDATA($uri);
        
        // while it would be nice to use parse_url(), that's specifically
        // for HTTP and thus won't work for our generic URI parsing
        
        // according to the RFC... (but this cuts corners, i.e. non-validating)
        $regexp = '!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!';
        //           12            3  4          5       6  7        8 9
        $matches = array();
        
        $result = preg_match($regexp, $uri, $matches);
        
        if (!$result) return ''; // wow, that's very strange
        
        // seperate out parts
        $scheme     = !empty($matches[1]) ? $matches[2] : null;
        $authority  = !empty($matches[3]) ? $matches[4] : null;
        $path       = $matches[5]; // always present
        $query      = !empty($matches[6]) ? $matches[7] : null;
        $fragment   = !empty($matches[8]) ? $matches[9] : null;
        
        // okay, no need to validate the scheme since we do that when we
        // retrieve the specific scheme object from the registry
        $scheme = ctype_lower($scheme) ? $scheme : strtolower($scheme);
        $registry = HTMLPurifier_URISchemeRegistry::instance();
        $scheme_obj = $registry->getScheme($scheme);
        
        if (!$scheme_obj) return ''; // invalid scheme, clean it out
        
        if ($authority !== null) {
            // validate authority
            $matches = array();
            
            $HEXDIG = '[A-Fa-f0-9]';
            $unreserved = 'A-Za-z0-9-._~';
            $sub_delims = '!$&\'()';
            $h16 = "{$HEXDIG}{1,4}";
            $dec_octet = '(?:25[0-5]|2[0-4]\d|1\d\d|1\d|[0-9])';
            $IPv4address = "$dec_octet.$dec_octet.$dec_octet.$dec_octet";
            $ls32 = "(?:$h16:$h16|$IPv4address)";
            $IPvFuture = "v$HEXDIG+\.[:$unreserved$sub_delims]+";
            $IPv6Address = "(?:".
                                        "(?:$h16:){6}$ls32" .
                                     "|::(?:$h16:){5}$ls32" .
                            "|(?:$h16)?::(?:$h16:){4}$ls32" .
                "|(?:(?:$h16:){1}$h16)?::(?:$h16:){3}$ls32" .
                "|(?:(?:$h16:){2}$h16)?::(?:$h16:){2}$ls32" .
                "|(?:(?:$h16:){3}$h16)?::(?:$h16:){1}$ls32" .
                "|(?:(?:$h16:){4}$h16)?::$ls32" .
                "|(?:(?:$h16:){5}$h16)?::$h16" .
                "|(?:(?:$h16:){6}$h16)?::" .
                ")";
            $IP_literal = "\[(?:$IPvFuture|$IPv6Address)\]";
            $regexp = "/^(([^@]+)@)?(\[$IP_literal\]|[^:]*)(:(\d*))?/";
            
            preg_match($regexp, $authority, $matches);
            $userinfo   = !empty($matches[1]) ? $matches[2] : null;
            $host       = !empty($matches[3]) ? $matches[3] : null;
            $port       = !empty($matches[4]) ? $matches[5] : null;
            
            if ($port !== null) {
                if (!ctype_digit($port) || $port < 1 || $port > 65535) {
                    $port = null;
                }
            }
            $authority =
                ($userinfo === null ? '' : ($userinfo . '@')) .
                $host .
                ($port === null ? '' : (':' . $port));
        }
        
        list($authority, $path, $query, $fragment) = 
        $scheme_obj->validateComponents($authority, $path, $query, $fragment);
        
        
        
    }
}

?>