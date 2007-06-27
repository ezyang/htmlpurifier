<?php

require_once 'HTMLPurifier/Token.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';
require_once 'HTMLPurifier/AttrTransform/Lang.php';

class HTMLPurifier_AttrTransform_LangTest
    extends HTMLPurifier_AttrTransformHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_Lang();
    }
    
    function test() {
        
        // leave non-lang'ed elements alone
        $this->assertResult(array(), true);
        
        // copy lang to xml:lang
        $this->assertResult(
            array('lang' => 'en'),
            array('lang' => 'en', 'xml:lang' => 'en')
        );
        
        // preserve attributes
        $this->assertResult(
            array('src' => 'vert.png', 'lang' => 'fr'),
            array('src' => 'vert.png', 'lang' => 'fr', 'xml:lang' => 'fr')
        );
        
        // copy xml:lang to lang
        $this->assertResult(
            array('xml:lang' => 'en'),
            array('xml:lang' => 'en', 'lang' => 'en')
        );
        
        // both set, override lang with xml:lang
        $this->assertResult(
            array('lang' => 'fr', 'xml:lang' => 'de'),
            array('lang' => 'de', 'xml:lang' => 'de')
        );
        
    }
    
}

