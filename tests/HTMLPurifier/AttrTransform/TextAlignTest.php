<?php

require_once 'HTMLPurifier/AttrTransform/TextAlign.php';

class HTMLPurifier_AttrTransform_TextAlignTest extends HTMLPurifier_AttrTransformHarness
{
    
    function test() {
        
        $this->transform = new HTMLPurifier_AttrTransform_TextAlign();
        
        $inputs = array();
        $expect = array();
        
        // leave empty arrays alone
        $inputs[0] = array();
        $expect[0] = true;
        
        // leave arrays without interesting stuff alone
        $inputs[1] = array('style' => 'font-weight:bold;');
        $expect[1] = true;
        
        // test each of the conversions
        
        $inputs[2] = array('align' => 'left');
        $expect[2] = array('style' => 'text-align:left;');
        
        $inputs[3] = array('align' => 'right');
        $expect[3] = array('style' => 'text-align:right;');
        
        $inputs[4] = array('align' => 'center');
        $expect[4] = array('style' => 'text-align:center;');
        
        $inputs[5] = array('align' => 'justify');
        $expect[5] = array('style' => 'text-align:justify;');
        
        // drop garbage value
        $inputs[6] = array('align' => 'invalid');
        $expect[6] = array();
        
        // test CSS munging
        $inputs[7] = array('align' => 'left', 'style' => 'font-weight:bold;');
        $expect[7] = array('style' => 'text-align:left;font-weight:bold;');
        
        // test case insensitivity
        $inputs[4] = array('align' => 'CENTER');
        $expect[4] = array('style' => 'text-align:center;');
        
        $this->assertTransform($inputs, $expect);
        
    }
    
}

?>