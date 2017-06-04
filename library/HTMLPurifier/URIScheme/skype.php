<?php

// Very relaxed, skype URIs allow everything

/**
 * Validates skype (proprietary Skype/Microsoft scheme) by doing nothing
 */

class HTMLPurifier_URIScheme_skype extends HTMLPurifier_URIScheme
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
        return true;
    }
}

// vim: et sw=4 sts=4
