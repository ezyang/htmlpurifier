<?php

require_once 'HTMLPurifier/ComplexHarness.php';

class HTMLPurifier_AttrTransformHarness extends HTMLPurifier_ComplexHarness
{
    
    function setUp() {
        parent::setUp();
        $this->func = 'transform';
    }
    
}

