<?php

require_once 'HTMLPurifier/AttrTransform/BdoDir.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_BdoDirTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_BdoDir();
    }
    
    function test() {
        
        $this->assertResult( array(), array('dir' => 'ltr') );
        
        // leave existing dir alone
        $this->assertResult( array('dir' => 'rtl') );
        
        // use a different default
        $this->assertResult(
            array(),
            array('dir' => 'rtl'),
            array('Attr.DefaultTextDir' => 'rtl')
        );
        
    }
    
}

