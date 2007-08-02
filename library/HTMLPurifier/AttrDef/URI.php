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
    
    var $parser, $percentEncoder;
    var $embedsResource;
    
    /**
     * @param $embeds_resource_resource Does the URI here result in an extra HTTP request?
     */
    function HTMLPurifier_AttrDef_URI($embeds_resource = false) {
        $this->parser = new HTMLPurifier_URIParser();
        $this->percentEncoder = new HTMLPurifier_PercentEncoder();
        $this->embedsResource = (bool) $embeds_resource;
    }
    
    function validate($uri, $config, &$context) {
        
        if ($config->get('URI', 'Disable')) return false;
        
        // initial operations
        $uri = $this->parseCDATA($uri);
        $uri = $this->percentEncoder->normalize($uri);
        
        // parse the URI
        $uri = $this->parser->parse($uri);
        if ($uri === false) return false;
        
        // add embedded flag to context for validators
        $context->register('EmbeddedURI', $this->embedsResource); 
        
        $ok = false;
        do {
            
            // generic validation
            $result = $uri->validate($config, $context);
            if (!$result) break;
            
            // chained validation
            $uri_def =& $config->getDefinition('URI');
            $result = $uri_def->filter($uri, $config, $context);
            if (!$result) break;
            
            // scheme-specific validation 
            $scheme_obj = $uri->getSchemeObj($config, $context);
            if (!$scheme_obj) break;
            if ($this->embedsResource && !$scheme_obj->browsable) break;
            $result = $scheme_obj->validate($uri, $config, $context);
            if (!$result) break;
            
            // survived gauntlet
            $ok = true;
            
        } while (false);
        
        $context->destroy('EmbeddedURI');
        if (!$ok) return false;
        
        // back to string
        $result = $uri->toString();
        
        // munge if necessary
        if (
            !is_null($uri->host) && // indicator for authority
            !empty($scheme_obj->browsable) &&
            !is_null($munge = $config->get('URI', 'Munge'))
        ) {
            $result = str_replace('%s', rawurlencode($result), $munge);
        }
        
        return $result;
        
    }
    
}


