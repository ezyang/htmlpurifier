<?php

/**
 * Validates tel (for phone numbers).
 *
 * The relevant specifications for this protocol are RFC 3966 and RFC 5341,
 * but this class takes a much simpler approach: we normalize phone
 * numbers so that they only include (possibly) a leading plus,
 * and then any number of digits and x'es.
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

        // Delete all non-numeric characters, commas, and non-x characters
        // from phone number, EXCEPT for a leading plus sign.
        $uri->path = self::normalizeNumber(
            // Normalize e(x)tension to lower-case
            str_replace('X', 'x', rawurldecode($uri->path)),
            'x,'
        );

        return true;
    }

    /**
     * Delete every character but digits and $keep from an already decoded
     * phone number, EXCEPT for a leading plus sign. Surrounding whitespace
     * is trimmed first, so it cannot push the plus off the front. Also used
     * by HTMLPurifier_URIScheme_sms.
     * @param string $number
     * @param string $keep Extra characters to keep, safe in a regex class
     * @return string
     */
    public static function normalizeNumber($number, $keep = '')
    {
        return preg_replace('/(?!^\+)[^\d' . $keep . ']/', '', trim($number));
    }
}

// vim: et sw=4 sts=4
