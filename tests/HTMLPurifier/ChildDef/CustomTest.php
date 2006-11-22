<?php

require_once 'HTMLPurifier/ChildDefHarness.php';
require_once 'HTMLPurifier/ChildDef/Custom.php';

class HTMLPurifier_ChildDef_CustomTest extends HTMLPurifier_ChildDefHarness
{
    
    function test() {
        
        $this->obj = new HTMLPurifier_ChildDef_Custom('(a,b?,c*,d+,(a,b)*)');
        
        $this->assertResult('', false);
        $this->assertResult('<a /><a />', false);
        
        $this->assertResult('<a /><b /><c /><d /><a /><b />');
        $this->assertResult('<a /><d>Dob</d><a /><b>foo</b>'.
          '<a href="moo" /><b>foo</b>');
        
    }
    
}

?>