<?php

// subclass this to add custom settings
class HTMLPurifier_Config
{
    
    // which ids do we not allow?
    var $attr_id_blacklist = array();
    
    //////////////////////////////////////////////////////////////////////////
    // all below properties have not been implemented yet
    
    // prefix all ids with this
    var $attr_id_prefix = '';
    
    // if there's a prefix, we may want to transparently rewrite the
    // URLs we parse too.  However, we can only do it when it's a pure
    // anchor link, so it's not foolproof
    var $attr_id_rewrite_urls = false;
    
    // determines how the classes array should be construed:
    // blacklist - allow allow except those in $classes_blacklist
    // whitelist - only allow those in $classes_whitelist
    // when one is chosen, the other has no effect
    var $attr_class_mode = 'blacklist';
    var $attr_class_blacklist = array();
    var $attr_class_whitelist = array();
    
    // designate whether or not to allow numerals in language code subtags
    // RFC 1766, the current standard referenced by XML, does not permit
    //           numbers, but,
    // RFC 3066, the superseding best practice standard since January 2001,
    //           permits them.
    // we allow numbers by default, although you generally never see them
    // at all.
    var $attr_lang_alpha = false;
    
    // max amount of pixels allowed to be specified
    var $attr_pixels_hmax = 600;  // horizontal context
    var $attr_pixels_vmax = 1200; // vertical context
    
    // allowed URI schemes
    var $uri_schemes = array(
        // based off of MediaWiki's default settings
        // the ones that definitely must be implemented (they're the same though)
        'http'  => true, // "Hypertext Transfer Protocol", nuf' said
        'https' => true, // HTTP over SSL (Secure Socket Layer)
        // quite useful, but not necessary
        'mailto' => true,// Email
        'ftp'   => true, // "File Transfer Protocol"
        'irc'   => true, // "Internet Relay Chat", usually needs another app
        // obscure
        'telnet' => true,// network protocol for non-secure remote terminal sessions
        // for Usenet, these two are similar, but distinct
        'nntp'  => true, // individual Netnews articles
        'news'  => true  // newsgroup or individual Netnews articles
        // gopher and worldwind excluded
    );
    
    // will munge all URIs to a different URI, which should redirect
    // the user to the applicable page. A urlencoded version of the URI
    // will replace any instances of %s in the string. One possible
    // string is 'http://www.google.com/url?q=%s'. Useful for preventing
    // pagerank from being sent to other sites
    var $uri_munge = false;
    
    // will add rel="nofollow" to all links, also helps prevent pagerank
    // from going around
    var $uri_add_relnofollow = false;
    
    // web root of the website, we'll try to auto-detect it. Something
    // like 'www.example.com/'???
    var $uri_webroot = null;
    
    // transform all relative URIs into their absolute forms, requires
    // $uri_webroot
    var $uri_make_absolute = false;
    
    // disables external links, requires $uri_webroot
    var $uri_disable_external = false;
    
    function createDefault() {
        $config = new HTMLPurifier_Config();
        return $config;
    }
    
}

?>