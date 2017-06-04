<?php

// Very relaxed, XMPP URIs allow everything

/**
 * Validates xmpp according to RFC 4622
 */

class HTMLPurifier_URIScheme_xmpp extends HTMLPurifier_URIScheme
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
