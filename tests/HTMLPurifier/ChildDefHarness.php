<?php

require_once 'HTMLPurifier/ComplexHarness.php';
require_once 'HTMLPurifier/ChildDef.php';

class HTMLPurifier_ChildDefHarness extends HTMLPurifier_ComplexHarness
{
    
    function setUp() {
        $this->obj       = null;
        $this->func      = 'validateChildren';
        $this->to_tokens = true;
        $this->to_html   = true;
    }
    
}


