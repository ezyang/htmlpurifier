<?php

class HTMLPurifier_URIFilter_IframeHostWhitelist extends HTMLPurifier_URIFilter
{
    public $name = 'IframeHostWhitelist';
    protected $whitelist = array();
    public function prepare($config) {
        $this->whitelist = $config->get('URI.IframeHostWhitelist');
        return true;
    }
    public function filter(&$uri, $config, $context) {
        if (!$context->get('EmbeddedURI', true)) return true;
        $token = $context->get('CurrentToken', true);
        if (!($token && $token->name == 'iframe')) return true;
        return in_array($uri->host, $this->whitelist);
    }
}

// vim: et sw=4 sts=4
