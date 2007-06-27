<?php

require_once 'HTMLPurifier/AttrTransform/EnumToCSS.php';
require_once 'HTMLPurifier/AttrTransformHarness.php';

class HTMLPurifier_AttrTransform_EnumToCSSTest extends HTMLPurifier_AttrTransformHarness
{
    
    function testRegular() {
        
        $this->obj = new HTMLPurifier_AttrTransform_EnumToCSS('align', array(
            'left'  => 'text-align:left;',
            'right' => 'text-align:right;'
        ));
        
        // leave empty arrays alone
        $this->assertResult( array() );
        
        // leave arrays without interesting stuff alone
        $this->assertResult( array('style' => 'font-weight:bold;') );
        
        // test each of the conversions
        
        $this->assertResult(
            array('align' => 'left'),
            array('style' => 'text-align:left;')
        );
        
        $this->assertResult(
            array('align' => 'right'),
            array('style' => 'text-align:right;')
        );
        
        // drop garbage value
        $this->assertResult(
            array('align' => 'invalid'),
            array()
        );
        
        // test CSS munging
        $this->assertResult(
            array('align' => 'left', 'style' => 'font-weight:bold;'),
            array('style' => 'text-align:left;font-weight:bold;')
        );
        
    }
    
    function testCaseInsensitive() {
        
        $this->obj = new HTMLPurifier_AttrTransform_EnumToCSS('align', array(
            'right' => 'text-align:right;'
        ));
        
        // test case insensitivity
        $this->assertResult(
            array('align' => 'RIGHT'),
            array('style' => 'text-align:right;')
        );
        
    }
    
    function testCaseSensitive() {
        
        $this->obj = new HTMLPurifier_AttrTransform_EnumToCSS('align', array(
            'right' => 'text-align:right;'
        ), true);
        
        // test case insensitivity
        $this->assertResult(
            array('align' => 'RIGHT'),
            array()
        );
        
    }
    
}

