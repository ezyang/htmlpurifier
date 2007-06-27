<?php

require_once 'HTMLPurifier/AttrTransform/Border.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';


class HTMLPurifier_AttrTransform_BorderTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_Border();
    }
    
    function test() {
        
        $this->assertResult( array() );
        
        $this->assertResult(
            array('border' => '1'),
            array('style' => 'border:1px solid;')
        );
        
        // once again, no validation done here, we expect CSS validator
        // to catch it
        $this->assertResult(
            array('border' => '10%'),
            array('style' => 'border:10%px solid;')
        );
        
        $this->assertResult(
            array('border' => '23', 'style' => 'font-weight:bold;'),
            array('style' => 'border:23px solid;font-weight:bold;')
        );
        
    }
    
}

