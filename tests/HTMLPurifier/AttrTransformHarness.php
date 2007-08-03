<?php

require_once 'HTMLPurifier/ComplexHarness.php';

class HTMLPurifier_AttrTransformHarness extends HTMLPurifier_ComplexHarness
{
    
    function setUp() {
        $this->func = 'transform';
    }
    
}

