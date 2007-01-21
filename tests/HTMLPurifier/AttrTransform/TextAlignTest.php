<?php

require_once 'HTMLPurifier/AttrTransform/TextAlign.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_TextAlignTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_TextAlign();
    }
    
    function test() {
        
        // leave empty arrays alone
        $this->assertResult( array() );
        
        // leave arrays without interesting stuff alone
        $this->assertResult( array('style' => 'font-weight:bold;') );
        
        // test each of the conversions
        
        $this->assertResult(
            array('align' => 'left'),
            array('style' => 'text-align:left;')
        );
        
        $this->assertResult(
            array('align' => 'right'),
            array('style' => 'text-align:right;')
        );
        
        $this->assertResult(
            array('align' => 'center'),
            array('style' => 'text-align:center;')
        );
        
        $this->assertResult(
            array('align' => 'justify'),
            array('style' => 'text-align:justify;')
        );
        
        // drop garbage value
        $this->assertResult(
            array('align' => 'invalid'),
            array()
        );
        
        // test CSS munging
        $this->assertResult(
            array('align' => 'left', 'style' => 'font-weight:bold;'),
            array('style' => 'text-align:left;font-weight:bold;')
        );
        
        // test case insensitivity
        $this->assertResult(
            array('align' => 'CENTER'),
            array('style' => 'text-align:center;')
        );
        
    }
    
}

?>