<?php

// pretty-printing with indentation would be pretty cool

require_once 'HTMLPurifier/Lexer.php';

HTMLPurifier_ConfigDef::define(
    'Core', 'CleanUTF8DuringGeneration', false, 'bool',
    'When true, HTMLPurifier_Generator will also check all strings it '.
    'escapes for UTF-8 well-formedness as a defense in depth measure. '.
    'This could cause a considerable performance impact, and is not '.
    'strictly necessary due to the fact that the Lexers should have '.
    'ensured that all the UTF-8 strings were well-formed.  Note that '.
    'the configuration value is only read at the beginning of '.
    'generateFromTokens.'
);

/**
 * Generates HTML from tokens.
 */
class HTMLPurifier_Generator
{
    
    /**
     * Bool cache of the CleanUTF8DuringGeneration directive.
     * @private
     */
    var $_clean_utf8 = false;
    
    /**
     * Generates HTML from an array of tokens.
     * @param $tokens Array of HTMLPurifier_Token
     * @param $config HTMLPurifier_Config object
     * @return Generated HTML
     * @note Only unit tests may omit configuration: internals MUST pass config
     */
    function generateFromTokens($tokens, $config = null) {
        $html = '';
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        $this->_clean_utf8 = $config->get('Core', 'CleanUTF8DuringGeneration');
        if (!$tokens) return '';
        foreach ($tokens as $token) {
            $html .= $this->generateFromToken($token);
        }
        return $html;
    }
    
    /**
     * Generates HTML from a single token.
     * @param $token HTMLPurifier_Token object.
     * @return Generated HTML
     */
    function generateFromToken($token) {
        if (!isset($token->type)) return '';
        if ($token->type == 'start') {
            $attr = $this->generateAttributes($token->attributes);
            return '<' . $token->name . ($attr ? ' ' : '') . $attr . '>';
            
        } elseif ($token->type == 'end') {
            return '</' . $token->name . '>';
            
        } elseif ($token->type == 'empty') {
            $attr = $this->generateAttributes($token->attributes);
             return '<' . $token->name . ($attr ? ' ' : '') . $attr . ' />';
            
        } elseif ($token->type == 'text') {
            return $this->escape($token->data);
            
        } else {
            return '';
            
        }
    }
    
    /**
     * Generates attribute declarations from attribute array.
     * @param $assoc_array_of_attributes Attribute array
     * @return Generate HTML fragment for insertion.
     */
    function generateAttributes($assoc_array_of_attributes) {
        $html = '';
        foreach ($assoc_array_of_attributes as $key => $value) {
            $html .= $key.'="'.$this->escape($value).'" ';
        }
        return rtrim($html);
    }
    
    /**
     * Escapes raw text data.
     * @param $string String data to escape for HTML.
     * @return String escaped data.
     */
    function escape($string) {
        if ($this->_clean_utf8) $string = HTMLPurifier_Lexer::cleanUTF8($string);
        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
    }
    
}

?>