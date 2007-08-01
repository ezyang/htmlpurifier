<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/URIParser.php';
require_once 'HTMLPurifier/URIScheme.php';
require_once 'HTMLPurifier/URISchemeRegistry.php';
require_once 'HTMLPurifier/AttrDef/URI/Host.php';
require_once 'HTMLPurifier/PercentEncoder.php';

HTMLPurifier_ConfigSchema::define(
    'URI', 'DefaultScheme', 'http', 'string',
    'Defines through what scheme the output will be served, in order to '.
    'select the proper object validator when no scheme information is present.'
);

HTMLPurifier_ConfigSchema::define(
    'URI', 'Host', null, 'string/null',
    'Defines the domain name of the server, so we can determine whether or '.
    'an absolute URI is from your website or not.  Not strictly necessary, '.
    'as users should be using relative URIs to reference resources on your '.
    'website.  It will, however, let you use absolute URIs to link to '.
    'subdomains of the domain you post here: i.e. example.com will allow '.
    'sub.example.com.  However, higher up domains will still be excluded: '.
    'if you set %URI.Host to sub.example.com, example.com will be blocked. '.
    'This directive has been available since 1.2.0.'
);

HTMLPurifier_ConfigSchema::define(
    'URI', 'DisableExternal', false, 'bool',
    'Disables links to external websites.  This is a highly effective '.
    'anti-spam and anti-pagerank-leech measure, but comes at a hefty price: no'.
    'links or images outside of your domain will be allowed.  Non-linkified '.
    'URIs will still be preserved.  If you want to be able to link to '.
    'subdomains or use absolute URIs, specify %URI.Host for your website. '.
    'This directive has been available since 1.2.0.'
);

HTMLPurifier_ConfigSchema::define(
    'URI', 'DisableExternalResources', false, 'bool',
    'Disables the embedding of external resources, preventing users from '.
    'embedding things like images from other hosts. This prevents '.
    'access tracking (good for email viewers), bandwidth leeching, '.
    'cross-site request forging, goatse.cx posting, and '.
    'other nasties, but also results in '.
    'a loss of end-user functionality (they can\'t directly post a pic '.
    'they posted from Flickr anymore). Use it if you don\'t have a '.
    'robust user-content moderation team. This directive has been '.
    'available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'URI', 'DisableResources', false, 'bool',
    'Disables embedding resources, essentially meaning no pictures. You can '.
    'still link to them though. See %URI.DisableExternalResources for why '.
    'this might be a good idea. This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'URI', 'Munge', null, 'string/null',
    'Munges all browsable (usually http, https and ftp) URI\'s into some URL '.
    'redirection service. Pass this directive a URI, with %s inserted where '.
    'the url-encoded original URI should be inserted (sample: '.
    '<code>http://www.google.com/url?q=%s</code>). '.
    'This prevents PageRank leaks, while being as transparent as possible '.
    'to users (you may also want to add some client side JavaScript to '.
    'override the text in the statusbar). Warning: many security experts '.
    'believe that this form of protection does not deter spam-bots. '.
    'You can also use this directive to redirect users to a splash page '.
    'telling them they are leaving your website. '.
    'This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'URI', 'HostBlacklist', array(), 'list',
    'List of strings that are forbidden in the host of any URI. Use it to '.
    'kill domain names of spam, etc. Note that it will catch anything in '.
    'the domain, so <tt>moo.com</tt> will catch <tt>moo.com.example.com</tt>. '.
    'This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'URI', 'Disable', false, 'bool',
    'Disables all URIs in all forms. Not sure why you\'d want to do that '.
    '(after all, the Internet\'s founded on the notion of a hyperlink). '.
    'This directive has been available since 1.3.0.'
);
HTMLPurifier_ConfigSchema::defineAlias('Attr', 'DisableURI', 'URI', 'Disable');

/**
 * Validates a URI as defined by RFC 3986.
 * @note Scheme-specific mechanics deferred to HTMLPurifier_URIScheme
 */
class HTMLPurifier_AttrDef_URI extends HTMLPurifier_AttrDef
{
    
    var $host, $parser;
    var $embeds_resource;
    
    /**
     * @param $embeds_resource_resource Does the URI here result in an extra HTTP request?
     */
    function HTMLPurifier_AttrDef_URI($embeds_resource = false) {
        $this->host = new HTMLPurifier_AttrDef_URI_Host();
        $this->parser = new HTMLPurifier_URIParser();
        $this->embeds_resource = (bool) $embeds_resource;
    }
    
    function validate($uri, $config, &$context) {
        
        static $PercentEncoder = null;
        if ($PercentEncoder === null) $PercentEncoder = new HTMLPurifier_PercentEncoder();
        
        if ($config->get('URI', 'Disable')) return false;
        
        // initial operations
        $uri = $this->parseCDATA($uri);
        $uri = $PercentEncoder->normalize($uri);
        
        // parse the URI
        $parsed_uri = $this->parser->parse($uri);
        if ($parsed_uri === false) return false;
        list($scheme, $userinfo, $host, $port, $path, $query, $fragment) = $parsed_uri;
        
        // retrieve the scheme object
        $registry =& HTMLPurifier_URISchemeRegistry::instance();
        $default_scheme = $config->get('URI', 'DefaultScheme');
        if ($scheme !== null) {
            // no need to validate the scheme's fmt since we do that when we
            // retrieve the specific scheme object from the registry
            $scheme = ctype_lower($scheme) ? $scheme : strtolower($scheme);
            $scheme_obj = $registry->getScheme($scheme, $config, $context);
            if (!$scheme_obj) return false; // invalid scheme, clean it out
        } else {
            // no scheme: retrieve the default one
            $scheme_obj = $registry->getScheme($default_scheme, $config, $context);
            if (!$scheme_obj) {
                // something funky happened to the default scheme object
                trigger_error(
                    'Default scheme object "' . $config->get('URI', 'DefaultScheme') . '" was not readable',
                    E_USER_WARNING
                );
                return false;
            }
        }
        if ($this->embeds_resource && !$scheme_obj->browsable) {
            // the URI we're processing embeds_resource a resource in the 
            // page, but the URI it references cannot be physically retrieved
            return false;
        }
        
        // validate host
        if ($host !== null) {
            // remove URI if it's absolute and we disabled externals or
            // if it's absolute and embedded and we disabled external resources
            unset($our_host);
            if (
                $config->get('URI', 'DisableExternal') ||
                (
                    $config->get('URI', 'DisableExternalResources') &&
                    $this->embeds_resource
                )
            ) {
                $our_host = $config->get('URI', 'Host');
                if ($our_host === null) return false;
            }
            $host = $this->host->validate($host, $config, $context);
            if ($host === false) $host = null;
            
            // check host against blacklist
            if ($this->checkBlacklist($host, $config, $context)) return false;
            
            // more lenient absolute checking
            if (isset($our_host)) {
                $host_parts = array_reverse(explode('.', $host));
                // could be cached
                $our_host_parts = array_reverse(explode('.', $our_host));
                foreach ($our_host_parts as $i => $discard) {
                    if (!isset($host_parts[$i])) return false;
                    if ($host_parts[$i] != $our_host_parts[$i]) return false;
                }
            }
        }
        
        // validate port
        if ($port !== null) {
            if ($port < 1 || $port > 65535) $port = null;
        }
        
        
        // query and fragment are quite simple in terms of definition:
        // *( pchar / "/" / "?" ), so define their validation routines
        // when we start fixing percent encoding
        
        
        
        // path gets to be validated against a hodge-podge of rules depending
        // on the status of authority and scheme, but it's not that important,
        // esp. since it won't be applicable to everyone
        
        
        
        // okay, now we defer execution to the subobject for more processing
        // note that $fragment is omitted
        list($userinfo, $host, $port, $path, $query) = 
            $scheme_obj->validateComponents(
                $userinfo, $host, $port, $path, $query, $config, $context
            );
        
        
        // reconstruct authority
        $authority = null;
        if (!is_null($userinfo) || !is_null($host) || !is_null($port)) {
            $authority = '';
            if($userinfo !== null) $authority .= $userinfo . '@';
            $authority .= $host;
            if($port !== null) $authority .= ':' . $port;
        } else {
            if ($default_scheme == $scheme) $scheme = null; // munge scheme off when unnecessary
        }
        
        // reconstruct the result
        $result = '';
        if ($scheme !== null) $result .= "$scheme:";
        if ($authority !== null) $result .= "//$authority";
        $result .= $path;
        if ($query !== null) $result .= "?$query";
        if ($fragment !== null) $result .= "#$fragment";
        
        // munge if necessary
        $munge = $config->get('URI', 'Munge');
        if (!empty($scheme_obj->browsable) && $munge !== null) {
            if ($authority !== null) {
                $result = str_replace('%s', rawurlencode($result), $munge);
            }
        }
        
        return $result;
        
    }
    
    /**
     * Checks a host against an array blacklist
     * @param $host Host to check
     * @param $config HTMLPurifier_Config instance
     * @param $context HTMLPurifier_Context instance
     * @return bool Is spam?
     */
    function checkBlacklist($host, &$config, &$context) {
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
    
}


