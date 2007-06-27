<?php

require_once 'HTMLPurifier/ChildDefHarness.php';
require_once 'HTMLPurifier/ChildDef/Required.php';

class HTMLPurifier_ChildDef_RequiredTest extends HTMLPurifier_ChildDefHarness
{
    
    function testParsing() {
        
        $def = new HTMLPurifier_ChildDef_Required('foobar | bang |gizmo');
        $this->assertIdentical($def->elements,
          array(
            'foobar' => true
           ,'bang'   => true
           ,'gizmo'  => true
          ));
        
        $def = new HTMLPurifier_ChildDef_Required(array('href', 'src'));
        $this->assertIdentical($def->elements,
          array(
            'href' => true
           ,'src'  => true
          ));
        
    }
    
    function testPCDATAForbidden() {
        
        $this->obj = new HTMLPurifier_ChildDef_Required('dt | dd');
        
        $this->assertResult('', false);
        $this->assertResult(
          '<dt>Term</dt>Text in an illegal location'.
             '<dd>Definition</dd><b>Illegal tag</b>',
          '<dt>Term</dt><dd>Definition</dd>');
        $this->assertResult('How do you do!', false);
        
        // whitespace shouldn't trigger it
        $this->assertResult("\n<dd>Definition</dd>       ");
        
        $this->assertResult(
          '<dd>Definition</dd>       <b></b>       ',
          '<dd>Definition</dd>              '
        );
        $this->assertResult("\t      ", false);
        
    }
    
    function testPCDATAAllowed() {
        
        $this->obj = new HTMLPurifier_ChildDef_Required('#PCDATA | b');
        
        $this->assertResult('<b>Bold text</b><img />', '<b>Bold text</b>');
        
        // with child escaping on
        $this->assertResult(
            '<b>Bold text</b><img />',
            '<b>Bold text</b>&lt;img /&gt;',
            array(
              'Core.EscapeInvalidChildren' => true
            )
        );
        
    }
    
}

