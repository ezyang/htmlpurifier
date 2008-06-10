<?php

class HTMLPurifier_URIFilter_SecureMunge extends HTMLPurifier_URIFilter
{
    public $name = 'SecureMunge';
    public $post = true;
    private $target, $secretKey, $parser;
    public function prepare($config) {
        $this->target = $config->get('URI', 'SecureMunge');
        $this->secretKey = $config->get('URI', 'SecureMungeSecretKey');
        $this->parser = new HTMLPurifier_URIParser();
        if (!$this->secretKey) {
            trigger_error('URI.SecureMunge is being ignored due to lack of value for URI.SecureMungeSecretKey', E_USER_WARNING);
            return false;
        }
        return true;
    }
    public function filter(&$uri, $config, $context) {
        if (!$this->target || !$this->secretKey) return true;
        if ($context->get('EmbeddedURI', true)) return true; // abort for embedded URIs
        $scheme_obj = $uri->getSchemeObj($config, $context);
        if (!$scheme_obj) return true; // ignore unknown schemes, maybe another postfilter did it
        if (is_null($uri->host) || empty($scheme_obj->browsable) || $context->get('EmbeddedURI', true)) {
            return true;
        }
        $string = $uri->toString();
        $checksum = sha1($this->secretKey . ':' . $string);
        $new_uri = str_replace('%s', rawurlencode($string), $this->target);
        $new_uri = str_replace('%t', $checksum, $new_uri);
        $uri = $this->parser->parse($new_uri); // overwrite
        return true;
    }
}
