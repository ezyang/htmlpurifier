<?php

class HTML_Generator
{
    
    function generateFromTokens($tokens) {
        $html = '';
        foreach ($tokens as $token) {
            $html .= $this->generateFromToken($token);
        }
        return $html;
    }
    
    function generateFromToken($token) {
        if (is_a($token, 'MF_StartTag')) {
            $attr = $this->generateAttributes($token->attributes);
            return '<' . $token->name . ($attr ? ' ' : '') . $attr . '>';
            
        } elseif (is_a($token, 'MF_EndTag')) {
            return '</' . $token->name . '>';
            
        } elseif (is_a($token, 'MF_EmptyTag')) {
            $attr = $this->generateAttributes($token->attributes);
             return '<' . $token->name . ' ' . $attr . ' />';
            
        } elseif (is_a($token, 'MF_Text')) {
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