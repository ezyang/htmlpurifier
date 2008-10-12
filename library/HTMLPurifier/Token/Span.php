<?php

/**
 * Abstract class of a span token (start, end or empty), and its behavior.
 */
class HTMLPurifier_Token_Span extends HTMLPurifier_Token
{
    public $name = '#PCDATA'; /**< PCDATA tag name compatible with DTD. */
    public $attr = array(); /**< Parsed character data of text. */

    /**
     * Constructor, accepts data and determines if it is whitespace.
     *
     * @param $data String parsed character data.
     */
    public function __construct($attr = array(), $line = null) {
        foreach ($attr as $key => $value) {
            // normalization only necessary when key is not lowercase
            if (!ctype_lower($key)) {
                $new_key = strtolower($key);
                if (!isset($attr[$new_key])) {
                    $attr[$new_key] = $attr[$key];
                }
                if ($new_key !== $key) {
                    unset($attr[$key]);
                }
            }
        }
        $this->attr = $attr;
        $this->line = $line;
    }
}
