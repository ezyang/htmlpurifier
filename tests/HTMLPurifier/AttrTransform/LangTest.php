<?php

require_once 'HTMLPurifier/Token.php';
require_once 'HTMLPurifier/AttrTransform/Lang.php';

class HTMLPurifier_AttrTransform_LangTest extends UnitTestCase
{
    
    function test() {
        
        $transform = new HTMLPurifier_AttrTransform_Lang();
        
        $inputs = array();
        $expect = array();
        
        // leave non-lang'ed elements alone
        $inputs[0] = new HTMLPurifier_Token_Start('b');
        $expect[0] = $inputs[0];
        
        // copy lang to xml:lang
        $inputs[1] = new HTMLPurifier_Token_Start('span',
                        array('lang' => 'en'));
        $expect[1] = new HTMLPurifier_Token_Start('span',
                        array('lang' => 'en',
                              'xml:lang' => 'en'));
        
        // empty tags must work too, also test attribute preservation
        $inputs[2] = new HTMLPurifier_Token_Empty('img',
                        array('src' => 'seine.png',
                              'lang' => 'fr'));
        $expect[2] = new HTMLPurifier_Token_Empty('img',
                        array('src' => 'seine.png',
                              'lang' => 'fr',
                              'xml:lang' => 'fr'));
        
        // copy xml:lang to lang
        $inputs[3] = new HTMLPurifier_Token_Start('span',
                        array('xml:lang' => 'en'));
        $expect[3] = new HTMLPurifier_Token_Start('span',
                        array('lang' => 'en',
                              'xml:lang' => 'en'));
        
        // both set, override lang with xml:lang
        $inputs[4] = new HTMLPurifier_Token_Start('span',
                        array('lang' => 'fr',
                              'xml:lang' => 'de'));
        $expect[4] = new HTMLPurifier_Token_Start('span',
                        array('lang' => 'de',
                              'xml:lang' => 'de'));
        
        foreach ($inputs as $i => $input) {
            $result = $transform->transform($input);
            $this->assertEqual($expect[$i], $result, "Test $i: %s");
        }
        
    }
    
}

?>