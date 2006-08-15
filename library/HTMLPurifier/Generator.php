<?php

// pretty-printing with indentation would be pretty cool

class HTMLPurifier_Generator
{
    
    // only unit tests may omit configuration: internals MUST pass config
    function generateFromTokens($tokens, $config = null) {
        $html = '';
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        if (!$tokens) return '';
        foreach ($tokens as $token) {
            $html .= $this->generateFromToken($token, $config);
        }
        return $html;
    }
    
    function generateFromToken($token, $config) {
        if (!isset($token->type)) return '';
        if ($token->type == 'start') {
            $attr = $this->generateAttributes($token->attributes, $config);
            return '<' . $token->name . ($attr ? ' ' : '') . $attr . '>';
            
        } elseif ($token->type == 'end') {
            return '</' . $token->name . '>';
            
        } elseif ($token->type == 'empty') {
            $attr = $this->generateAttributes($token->attributes, $config);
             return '<' . $token->name . ($attr ? ' ' : '') . $attr . ' />';
            
        } elseif ($token->type == 'text') {
            return htmlspecialchars($token->data, ENT_COMPAT, 'UTF-8');
            
        } else {
            return '';
            
        }
    }
    
    function generateAttributes($assoc_array_of_attributes, $config) {
        $html = '';
        foreach ($assoc_array_of_attributes as $key => $value) {
            $html .= $key.'="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" ';
        }
        return rtrim($html);
    }
    
}

?>