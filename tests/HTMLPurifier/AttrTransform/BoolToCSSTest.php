<?php

require_once 'HTMLPurifier/AttrTransform/BoolToCSS.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_BoolToCSSTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_BoolToCSS('foo', 'bar:3in;');
    }
    
    function test() {
        
        $this->assertResult( array() );
        
        $this->assertResult(
            array('foo' => 'foo'),
            array('style' => 'bar:3in;')
        );
        
        // boolean attribute just has to be set: we don't care about
        // anything else
        $this->assertResult(
            array('foo' => 'no'),
            array('style' => 'bar:3in;')
        );
        
        $this->assertResult(
            array('foo' => 'foo', 'style' => 'background-color:#F00;'),
            array('style' => 'bar:3in;background-color:#F00;')
        );
        
    }
    
}

