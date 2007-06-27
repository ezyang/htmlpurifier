<?php

require_once 'HTMLPurifier/ErrorsHarness.php';

class HTMLPurifier_Strategy_ErrorsHarness extends HTMLPurifier_ErrorsHarness
{
    
    // needs to be defined
    function getStrategy() {}
    
    function invoke($input) {
        $strategy = $this->getStrategy();
        $lexer = new HTMLPurifier_Lexer_DirectLex();
        $tokens = $lexer->tokenizeHTML($input, $this->config, $this->context);
        $strategy->execute($tokens, $this->config, $this->context);
    }
    
}

