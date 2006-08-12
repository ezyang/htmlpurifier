<?php

require_once 'HTMLPurifier/URIScheme.php';
require_once 'HTMLPurifier/URISchemeRegistry.php';

HTMLPurifier_ConfigDef::define(
    'URI', 'DefaultScheme', 'http',
    'Defines through what scheme the output will be served, in order to '.
    'select the proper object validator when no scheme information is present.'
);

class HTMLPurifier_AttrDef_URI extends HTMLPurifier_AttrDef
{
    
    function validate($uri, $config, &$context) {
        
        // We'll write stack-based parsers later, for now, use regexps to
        // get things working as fast as possible (irony)
        
        // parse as CDATA
        $uri = $this->parseCDATA($uri);
        
        // while it would be nice to use parse_url(), that's specifically
        // for HTTP and thus won't work for our generic URI parsing
        
        // according to the RFC... (but this cuts corners, i.e. non-validating)
        $r_URI = '!^(([^:/?#]+):)?(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?!';
        //           12            3  4          5       6  7        8 9
        
        $matches = array();
        $result = preg_match($r_URI, $uri, $matches);
        
        if (!$result)  return '';
        
        // seperate out parts
        $scheme     = !empty($matches[1]) ? $matches[2] : null;
        $authority  = !empty($matches[3]) ? $matches[4] : null;
        $path       = $matches[5]; // always present
        $query      = !empty($matches[6]) ? $matches[7] : null;
        $fragment   = !empty($matches[8]) ? $matches[9] : null;
        
        
        
        $registry =& HTMLPurifier_URISchemeRegistry::instance();
        if ($scheme !== null) {
            // no need to validate the scheme's fmt since we do that when we
            // retrieve the specific scheme object from the registry
            $scheme = ctype_lower($scheme) ? $scheme : strtolower($scheme);
            $scheme_obj =& $registry->getScheme($scheme, $config);
            if (!$scheme_obj) return ''; // invalid scheme, clean it out
        } else {
            $scheme_obj =& $registry->getScheme($config->get('URI', 'DefaultScheme'), $config);
        }
        
        
        
        if ($authority !== null) {
            
            // define regexps
            // this stuff may need to be factored out so Email can get to it
            
            $HEXDIG = '[A-Fa-f0-9]';
            $unreserved = 'A-Za-z0-9-._~'; // make sure you wrap with []
            $sub_delims = '!$&\'()'; // needs []
            $pct_encoded = "%$HEXDIG$HEXDIG";
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
            $r_userinfo = "(?:[$unreserved$sub_delims:]|$pct_encoded)*";
            
            // IPv6 is broken
            $r_authority = "/^(($r_userinfo)@)?(\[$IP_literal\]|[^:]*)(:(\d*))?/";
            $matches = array();
            preg_match($r_authority, $authority, $matches);
            // overloads regexp!
            $userinfo   = !empty($matches[1]) ? $matches[2] : null;
            $host       = !empty($matches[3]) ? $matches[3] : null;
            $port       = !empty($matches[4]) ? $matches[5] : null;
            
            // validate port
            if ($port !== null) {
                $port = (int) $port;
                if ($port < 1 || $port > 65535) $port = null;
            }
            
            // userinfo and host are validated within the regexp
            
            // regenerate authority
            $authority =
                ($userinfo === null ? '' : ($userinfo . '@')) .
                $host .
                ($port === null ? '' : (':' . $port));
        }
        
        
        // query and fragment are quite simple in terms of definition:
        // *( pchar / "/" / "?" ), so define their validation routines
        // when we start fixing percent encoding
        
        
        
        // path gets to be validated against a hodge-podge of rules depending
        // on the status of authority and scheme, but it's not that important,
        // esp. since it won't be applicable to everyone
        
        
        
        // okay, now we defer execution to the subobject for more processing
        list($authority, $path, $query, $fragment) = 
        $scheme_obj->validateComponents($authority, $path, $query, $fragment);
        
        
        
        // reconstruct the result
        $result = '';
        if ($scheme !== null) $result .= "$scheme:";
        if ($authority !== null) $result .= "//$authority";
        $result .= $path;
        if ($query !== null) $result .= "?$query";
        if ($fragment !== null) $result .= "#$fragment";
        
        return $result;
        
    }
    
}

?>
