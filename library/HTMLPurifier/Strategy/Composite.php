<?php

require_once 'HTMLPurifier/Strategy.php';

class HTMLPurifier_Strategy_Composite
{
    
    var $strategies = array();
    
    function HTMLPurifier_Strategy_Composite() {
        trigger_error('Attempt to instantiate abstract object', E_USER_ERROR);
    }
    
    function execute($tokens) {
        foreach ($this->strategies as $strategy) {
            $tokens = $strategy->execute($tokens);
        }
        return $tokens;
    }
    
}

?>