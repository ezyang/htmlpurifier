<?php

/**
 * Validates tel (for phone numbers) according to RFC 3966 and RFC 5341
 */

class HTMLPurifier_URIScheme_tel extends HTMLPurifier_URIScheme
{
    /**
     * @type bool
     */
    public $browsable = false;

    /**
     * @type bool
     */
    public $may_omit_host = true;

    /**
     * @param HTMLPurifier_URI $uri
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool
     */
    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        $uri->host     = null;
        $uri->port     = null;
        
        // Simplify phone number
        $uri->path = preg_replace('/(?!^\+)[^\dx]/', '', str_replace('X', 'x', $uri->path));
        
        // Regex from: http://snipplr.com/view/11540/regex-for-tel-uris/
        return true;
    }
}

// vim: et sw=4 sts=4
