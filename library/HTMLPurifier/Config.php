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
    
    function createDefault() {
        $config = new HTMLPurifier_Config();
        return $config;
    }
    
}

?>