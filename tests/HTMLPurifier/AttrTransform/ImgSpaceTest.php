<?php

require_once 'HTMLPurifier/AttrTransform/ImgSpace.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';


class HTMLPurifier_AttrTransform_ImgSpaceTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
    }
    
    function testVertical() {
        
        $this->obj = new HTMLPurifier_AttrTransform_ImgSpace('vspace');
        
        $this->assertResult( array() );
        
        $this->assertResult(
            array('vspace' => '1'),
            array('style' => 'margin-top:1px;margin-bottom:1px;')
        );
        
        // no validation done here, we expect CSS validator to catch it
        $this->assertResult(
            array('vspace' => '10%'),
            array('style' => 'margin-top:10%px;margin-bottom:10%px;')
        );
        
        $this->assertResult(
            array('vspace' => '23', 'style' => 'font-weight:bold;'),
            array('style' => 'margin-top:23px;margin-bottom:23px;font-weight:bold;')
        );
        
    }
    
    function testHorizontal() {
        $this->obj = new HTMLPurifier_AttrTransform_ImgSpace('hspace');
        $this->assertResult(
            array('hspace' => '1'),
            array('style' => 'margin-left:1px;margin-right:1px;')
        );
    }
    
    function testInvalid() {
        $this->expectError('ispace is not valid space attribute');
        $this->obj = new HTMLPurifier_AttrTransform_ImgSpace('ispace');
        $this->assertResult(
            array('ispace' => '1'),
            array()
        );
    }
    
}

