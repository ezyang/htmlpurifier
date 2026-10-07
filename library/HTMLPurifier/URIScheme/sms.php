<?php

/**
 * Validates sms (for text messaging).
 *
 * The relevant specification for this protocol is RFC 5724, which spells
 * the body parameter sms:number?body=message. The sms:number&body=message
 * form is common on the web, so we read both, but always write the RFC
 * form. We drop every parameter but body, and the fragment.
 *
 * RFC 5724 allows a comma-separated list of recipients, so we keep every
 * one of them, normalized as tel normalizes a number but without "x"
 * extensions. A recipient left with no digit at all is dropped, and a URI
 * left with no recipient is rejected.
 *
 * RFC 5724 has no authority, but sms://number shows up on the web too;
 * HTMLPurifier_URI::validate() moves such a number back into the path.
 *
 * Note we read the body after %URI.AllowedSymbols has been applied, so a
 * configuration that drops "&" or "=" from it encodes the delimiters we
 * look for and the body goes with them, leaving the number.
 */

class HTMLPurifier_URIScheme_sms extends HTMLPurifier_URIScheme
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
        // sms has no use for a fragment
        $uri->fragment = null;

        // parse: "&" is no query delimiter, so this all lands in the path
        $params = explode('&', $uri->path);
        $recipients = $this->parseRecipients(array_shift($params));

        // nobody to send it to
        if (!$recipients) {
            return false;
        }

        $body = $this->parseBody($params);
        if (!is_null($body)) {
            // a "?" in a path body is part of the message, but the parser
            // split everything after it off into the query
            if (!is_null($uri->query)) {
                $body .= '?' . rawurldecode($uri->query);
            }
        } elseif (!is_null($uri->query)) {
            $body = $this->parseBody(explode('&', $uri->query));
        }

        // write back out, always in the RFC 5724 form. Encoding the body
        // keeps it from escaping the href (the generator escapes it again on
        // output); an empty body keeps its parameter.
        $uri->path  = implode(',', $recipients);
        $uri->query = is_null($body) ? null : 'body=' . rawurlencode($body);

        return true;
    }

    /**
     * Parse an RFC 5724 comma-separated recipient list into its normalized
     * numbers, dropping the ones left with no digits.
     * @param string $list
     * @return string[]
     */
    private function parseRecipients($list)
    {
        // Decode before splitting, or an encoded comma is not read as the
        // separator it is and two recipients fuse into one wrong number.
        $recipients = array();
        foreach (explode(',', rawurldecode($list)) as $candidate) {
            // Unlike tel we drop "x" extension syntax; SMS has no extensions
            $number = HTMLPurifier_URIScheme_tel::normalizeNumber($candidate);
            if (strpbrk($number, '0123456789') !== false) {
                $recipients[] = $number;
            }
        }
        return $recipients;
    }

    /**
     * Decoded value of the first 'body' out of a list of name=value pairs,
     * or null. RFC 5234 makes the field name case-insensitive, so we take it
     * in any case.
     * @param string[] $params
     * @return string|null
     */
    private function parseBody($params)
    {
        foreach ($params as $param) {
            if (strpos($param, '=') === false) {
                continue;
            }
            list($param_name, $param_value) = explode('=', $param, 2);
            // sms:5555&amp;amp;body=... (escaped twice, as some CMSes do)
            // leaves "amp;" glued to the name once the lexer decodes it
            $param_name = preg_replace('/^(?:amp;)+/i', '', $param_name);
            if (strtolower($param_name) === 'body') {
                return rawurldecode($param_value);
            }
        }
        return null;
    }
}
