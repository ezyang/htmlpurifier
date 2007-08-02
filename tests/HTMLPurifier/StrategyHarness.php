<?php

require_once 'HTMLPurifier/ComplexHarness.php';

class HTMLPurifier_StrategyHarness extends HTMLPurifier_ComplexHarness
{
    
    function setUp() {
        parent::setUp();
        $this->func      = 'execute';
        $this->to_tokens = true;
        $this->to_html   = true;
    }
    
}

