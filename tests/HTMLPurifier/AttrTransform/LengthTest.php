<?php

require_once 'HTMLPurifier/AttrTransform/Length.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_LengthTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_Length('width');
    }
    
    function test() {
        $this->assertResult( array() );
        $this->assertResult(
            array('width' => '10'),
            array('style' => 'width:10px;')
        );
        $this->assertResult(
            array('width' => '10%'),
            array('style' => 'width:10%;')
        );
        $this->assertResult(
            array('width' => '10%', 'style' => 'font-weight:bold'),
            array('style' => 'width:10%;font-weight:bold')
        );
        // this behavior might change
        $this->assertResult(
            array('width' => 'asdf'),
            array('style' => 'width:asdf;')
        );
    }
    
}

