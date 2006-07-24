<?php

require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_StrategyAbstractTest extends UnitTestCase
{
    
    var $lex, $gen;
    
    function HTMLPurifier_StrategyAbstractTest() {
        $this->UnitTestCase();
        
        // we can't use the DOM lexer since it does too much stuff
        // automatically, however, we should be able to use it
        // interchangeably if we wanted to...
        
        if (true) {
            $this->lex = new HTMLPurifier_Lexer_DirectLex();
        } else {
            require_once 'HTMLPurifier/Lexer/DOMLex.php';
            $this->lex = new HTMLPurifier_Lexer_DOMLex();
        }
        
        $this->gen = new HTMLPurifier_Generator();
    }
    
}

?>