<?php

/**
 * Validates sms (for text messaging) using a security-oriented restricted
 * subset of RFC 5724.
 *
 * Each recipient must be either a global number (a leading plus followed
 * by digits) or a legacy bare digit string such as a short code.
 * The visual separators "-", ".", "(" and ")" carry no meaning (RFC 3966)
 * and are removed. This is deliberately narrower than the full RFC 3966
 * telephone-subscriber grammar: local numbers with phone-context and
 * recipient parameters such as ";ext=" are rejected rather than
 * interpreted. Anything else in the recipient -- spaces, letters, or
 * other punctuation -- also fails validation. A sanitizer must not
 * manufacture a different destination out of malformed input, so
 * malformed recipients are rejected, not repaired. Comma-separated
 * multiple recipients (RFC 5724) are supported; each is validated
 * independently.
 *
 * The message uses the "?body=" field, matched case-insensitively. Its
 * value is kept as received; percent-encoding has already been
 * canonicalized by the parser and generic validation before the scheme
 * runs, as for every other scheme's components (compare mailto's
 * query fields, which are likewise passed through).
 * The body must occur at most once (RFC 5724):
 * duplicates are rejected. Other fields are dropped as a deliberate
 * allowlist policy: RFC 5724 permits future extension fields, so an
 * extension-aware receiver could understand a field this validator does
 * not; the sanitizer keeps only the field whose meaning it knows instead
 * of passing unknown semantics through.
 *
 * Compatibility: the undocumented iOS convention "sms:<recipient>&body=<text>"
 * (no "?") is accepted and preserved as written. It is not RFC 5724 syntax;
 * the "?" form is canonical. The ";body=" variant is deliberately not
 * accepted: some clients treat ";" as a recipient separator, which makes
 * it unsafe as a cross-platform compatibility syntax.
 *
 * Like the other non-hierarchical schemes, an authority component is
 * discarded; the strict recipient check then rejects the "sms://..." form
 * in practice.
 *
 * Note we read the fields after %URI.AllowedSymbols has been applied, so a
 * configuration that drops "&" or "=" from it encodes the delimiters we
 * look for. In the "&body=" form they land in the recipient, which then
 * fails validation and the URI is rejected; in the "?body=" form the
 * field is unrecognizable and the body is dropped, leaving the recipient.
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

        $recipient = $uri->path;
        $body = null;
        $body_in_path = false;

        // "&" is no query delimiter (RFC 3986), so the iOS compatibility
        // form arrives with its fields in the path. Collect the fields
        // from both forms, tagged with where each came from so a body
        // found is written back in the form it arrived in.
        // Unknown fields are dropped as an allowlist policy.
        $parts = explode('&', $recipient);
        $recipient = array_shift($parts);
        $fields = array();
        foreach ($parts as $field) {
            $fields[] = array($field, true);
        }
        if (!is_null($uri->query)) {
            foreach (explode('&', $uri->query) as $field) {
                $fields[] = array($field, false);
            }
        }
        foreach ($fields as $tagged) {
            list($field, $in_path) = $tagged;
            $pos = strpos($field, '=');
            if ($pos === false) {
                continue;
            }
            // The parser has already decoded pct-encoded unreserved
            // characters, so the name arrives canonical; match it as-is.
            if (strtolower(substr($field, 0, $pos)) !== 'body') {
                continue;
            }
            if (!is_null($body)) {
                return false; // body must occur at most once (RFC 5724)
            }
            $body = substr($field, $pos + 1);
            $body_in_path = $in_path;
        }

        $validated = array();
        foreach (explode(',', $recipient) as $one) {
            // Visual separators are semantically ignorable (RFC 3966).
            $one = preg_replace('/[-.()]/', '', $one);
            // Optional leading plus, then digits: anything else is rejected.
            // /D so a trailing newline cannot satisfy the end anchor.
            if (!preg_match('/^\+?\d+$/D', $one)) {
                return false;
            }
            $validated[] = $one;
        }

        // An empty body keeps its parameter.
        $uri->path  = implode(',', $validated);
        $uri->query = null;
        if (!is_null($body)) {
            if ($body_in_path) {
                $uri->path .= '&body=' . $body;
            } else {
                $uri->query = 'body=' . $body;
            }
        }

        return true;
    }
}

// vim: et sw=4 sts=4
