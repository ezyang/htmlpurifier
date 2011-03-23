<?php

class HTMLPurifier_URIFilter_IframeWhitelistRegexp extends HTMLPurifier_URIFilter
{
    public $name = 'IframeWhitelistRegexp';
    protected $whitelist = array();
    public function prepare($config) {
        $this->whitelist = $config->get('URI.IframeWhitelistRegexp');
        return true;
    }
    public function filter(&$uri, $config, $context) {
        if (!$context->get('EmbeddedURI', true)) return true;
        $token = $context->get('CurrentToken', true);
        if (!($token && $token->name == 'iframe')) return true;
        
        foreach ($this->whitelist as $regexp) {
          if (preg_match($regexp, $uri->toString())) {
            // We have matched an allowed host, return true.
            return true;
          }
        }
        return false;
    }
}

// vim: et sw=4 sts=4
