<?php

require_once 'HTMLPurifier/Token.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';
require_once 'HTMLPurifier/AttrTransform/Lang.php';

class HTMLPurifier_AttrTransform_LangTest
    extends HTMLPurifier_AttrTransformHarness
{
    
    function test() {
        
        $this->transform = new HTMLPurifier_AttrTransform_Lang();
        
        $inputs = array();
        $expect = array();
        
        // leave non-lang'ed elements alone
        $inputs[0] = array();
        $expect[0] = true;
        
        // copy lang to xml:lang
        $inputs[1] = array('lang' => 'en');
        $expect[1] = array('lang' => 'en', 'xml:lang' => 'en');
        
        // preserve attributes
        $inputs[2] = array('src' => 'vert.png', 'lang' => 'fr');
        $expect[2] = array('src' => 'vert.png', 'lang' => 'fr', 'xml:lang' => 'fr');
        
        // copy xml:lang to lang
        $inputs[3] = array('xml:lang' => 'en');
        $expect[3] = array('lang' => 'en', 'xml:lang' => 'en');
        
        // both set, override lang with xml:lang
        $inputs[4] = array('lang' => 'fr', 'xml:lang' => 'de');
        $expect[4] = array('lang' => 'de', 'xml:lang' => 'de');
        
        $this->assertTransform($inputs, $expect);
        
    }
    
}

?>