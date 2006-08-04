<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/Config.php';

class HTMLPurifier_Strategy_Composite
{
    
    var $strategies = array();
    
    function HTMLPurifier_Strategy_Composite() {
        trigger_error('Attempt to instantiate abstract object', E_USER_ERROR);
    }
    
    function execute($tokens, $config = null) {
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        foreach ($this->strategies as $strategy) {
            $tokens = $strategy->execute($tokens, $config);
        }
        return $tokens;
    }
    
}

?>