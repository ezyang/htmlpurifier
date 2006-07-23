<?php

require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/Generator.php';

class HTMLPurifier
{
    
    var $lexer;
    var $definition;
    var $generator;
    
    function HTMLPurifier() {
        $this->lexer        = new HTMLPurifier_Lexer();
        $this->definition   = new HTMLPurifier_Definition();
        $this->generator    = new HTMLPurifier_Generator();
    }
    
    function purify($html) {
        $tokens = $this->lexer->tokenizeHTML($html);
        $tokens = $this->definition->purifyTokens($tokens);
        return $this->generator->generateFromTokens($tokens);
    }
    
}

?>