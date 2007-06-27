<?php

require_once 'HTMLPurifier/AttrTransform/Name.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_NameTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_Name();
    }
    
    function test() {
        $this->assertResult( array() );
        $this->assertResult(
            array('name' => 'free'),
            array('id' => 'free')
        );
        $this->assertResult(
            array('name' => 'tryit', 'id' => 'tobad'),
            array('id' => 'tobad')
        );
    }
    
}

