<?php

class HTMLPurifier_InjectorHarness extends HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_MakeWellFormed();
    }
    
}

