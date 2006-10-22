<?php

require_once 'HTMLPurifier/Harness.php';

class HTMLPurifier_AttrTransformHarness extends HTMLPurifier_Harness
{
    
    function setUp() {
        $this->func = 'transform';
    }
    
}

?>