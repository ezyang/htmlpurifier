<?php

require_once 'HTMLPurifier/AttrTransform/BdoDir.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_BdoDirTest extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_BdoDir();
    }
    
    function testAddDefaultDir() {
        $this->assertResult( array(), array('dir' => 'ltr') );
    }
    
    function testPreserveExistingDir() {
        $this->assertResult( array('dir' => 'rtl') );
    }
    
    function testAlternateDefault() {
        $this->config->set('Attr', 'DefaultTextDir', 'rtl');
        $this->assertResult(
            array(),
            array('dir' => 'rtl')
        );
        
    }
    
}

