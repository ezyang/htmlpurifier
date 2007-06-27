<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/Core.php';

class HTMLPurifier_HTMLModuleHarness extends HTMLPurifier_StrategyHarness
{
    function setup() {
        parent::setup();
        $this->obj = new HTMLPurifier_Strategy_Core();
    }
}

