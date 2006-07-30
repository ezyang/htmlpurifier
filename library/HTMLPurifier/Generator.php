<?php

class HTMLPurifier_Generator
{
    
    function generateFromTokens($tokens) {
        $html = '';
        if (!$tokens) return '';
        foreach ($tokens as $token) {
            $html .= $this->generateFromToken($token);
        }
        return $html;
    }
    
    function generateFromToken($token) {
        if ($token->type == 'start') {
            $attr = $this->generateAttributes($token->attributes);
            return '<' . $token->name . ($attr ? ' ' : '') . $attr . '>';
            
        } elseif ($token->type == 'end') {
            return '</' . $token->name . '>';
            
        } elseif ($token->type == 'empty') {
            $attr = $this->generateAttributes($token->attributes);
             return '<' . $token->name . ($attr ? ' ' : '') . $attr . ' />';
            
        } elseif ($token->type == 'text') {
            return htmlentities($token->data, ENT_COMPAT, 'UTF-8');
            
        } else {
            return '';
            
        }
    }
    
    function generateAttributes($assoc_array_of_attributes) {
        $html = '';
        foreach ($assoc_array_of_attributes as $key => $value) {
            $html .= $key.'="'.htmlentities($value, ENT_COMPAT, 'UTF-8').'" ';
        }
        return rtrim($html);
    }
    
}

?>