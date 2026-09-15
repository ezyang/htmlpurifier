<?php

/**
 * Validates sms (for text messaging).
 *
 * The relevant specification for this protocol is RFC 5724, which spells
 * the body parameter sms:number?body=message. The sms:number&body=message
 * form is common on the web, so we take both and keep whichever was
 * written: "&" leaves the body in the path, "?" leaves it in the query.
 * Numbers are normalized as in tel, and we drop every parameter but body.
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
        $authority     = $uri->host; // sms://NUMBER hides the recipient here
        $uri->userinfo = null;
        $uri->host     = null;
        $uri->port     = null;

        $phone_number = $uri->path;
        $body_content = null;
        $body_in_path = false;

        // "&" is no query delimiter, so this all lands in the path
        if (strpos($phone_number, '&') !== false) {
            $parts = explode('&', $phone_number);
            $phone_number = array_shift($parts);
            $body_content = $this->extractBody($parts);
            $body_in_path = !is_null($body_content);
        }

        // query body wins, so a mixed URI comes out in spec form
        if (!is_null($uri->query)) {
            $query_body = $this->extractBody(explode('&', $uri->query));
            if (!is_null($query_body)) {
                $body_content = $query_body;
                $body_in_path = false;
            }
        }

        $phone_number = $this->cleanPhoneNumber($phone_number);
        if ($phone_number === '' && !is_null($authority)) {
            $phone_number = $this->cleanPhoneNumber($authority);
        }

        // nobody to send it to
        if ($phone_number === '') {
            $body_content = null;
        }

        if ($body_content !== null) {
            $body_content = $this->sanitizeBody($body_content);
        }

        // an empty body keeps its parameter
        $uri->path  = $phone_number;
        $uri->query = null;
        if (!is_null($body_content)) {
            if ($body_in_path) {
                $uri->path .= '&body=' . $body_content;
            } else {
                $uri->query = 'body=' . $body_content;
            }
        }

        return true;
    }

    /**
     * Reduce a recipient to digits, EXCEPT for a leading plus sign.
     * @param string $candidate
     * @return string
     */
    private function cleanPhoneNumber($candidate)
    {
        return preg_replace('/(?!^\+)[^\d]/', '', rawurldecode($candidate));
    }

    /**
     * First 'body' value out of a list of name=value pairs, or null. RFC 5234
     * makes the field name case-insensitive, so we take it in any case.
     * @param string[] $params
     * @return string|null
     */
    private function extractBody($params)
    {
        foreach ($params as $param) {
            if (strpos($param, '=') === false) {
                continue;
            }
            list($param_name, $param_value) = explode('=', $param, 2);
            if (strtolower($param_name) === 'body') {
                return $param_value;
            }
        }
        return null;
    }

    /**
     * Percent-encode the body so it cannot escape the href; the generator
     * escapes it again on output. We decode first so purifying the same URI
     * twice does not stack encoding levels.
     * @param string $body
     * @return string
     */
    private function sanitizeBody($body)
    {
        return rawurlencode(rawurldecode($body));
    }
}
