<?php

require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_StrategyHarness extends UnitTestCase
{
    
    var $lex, $gen;
    
    function HTMLPurifier_StrategyHarness() {
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
    
    function assertStrategyWorks($strategy, $inputs, $expect, $config = array()) {
        foreach ($inputs as $i => $input) {
            $tokens = $this->lex->tokenizeHTML($input);
            if (!isset($config[$i])) {
                $config[$i] = HTMLPurifier_Config::createDefault();
            }
            $result_tokens = $strategy->execute($tokens, $config[$i]);
            $result = $this->gen->generateFromTokens($result_tokens, $config[$i]);
            $this->assertEqual($expect[$i], $result, "Test $i: %s");
            paintIf($result, $result != $expect[$i]);
        }
    }
    
}

?>