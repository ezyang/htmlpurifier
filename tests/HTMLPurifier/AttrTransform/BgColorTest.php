<?php

require_once 'HTMLPurifier/AttrTransform/BgColor.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_BgColorTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_BgColor();
    }
    
    function test() {
        
        $this->assertResult( array() );
        
        // we currently rely on the CSS validator to fix any problems.
        // This means that this transform, strictly speaking, supports
        // a superset of the functionality.
        
        $this->assertResult(
            array('bgcolor' => '#000000'),
            array('style' => 'background-color:#000000;')
        );
        
        $this->assertResult(
            array('bgcolor' => '#000000', 'style' => 'font-weight:bold'),
            array('style' => 'background-color:#000000;font-weight:bold')
        );
        
        // this may change when we natively support the datatype and
        // validate its contents before forwarding it on
        $this->assertResult(
            array('bgcolor' => '#F00'),
            array('style' => 'background-color:#F00;')
        );
        
    }
    
}

