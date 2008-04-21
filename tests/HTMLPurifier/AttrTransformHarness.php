<?php

class HTMLPurifier_AttrTransformHarness extends HTMLPurifier_ComplexHarness
{
    
    public function setUp() {
        parent::setUp();
        $this->func = 'transform';
    }
    
}

