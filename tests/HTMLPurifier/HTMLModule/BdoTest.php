<?php

require_once 'HTMLPurifier/HTMLModuleHarness.php';

class HTMLPurifier_HTMLModule_BdoTest extends HTMLPurifier_HTMLModuleHarness
{
    
    function test() {
        
        $this->setupScaffold('Bdo');
        
        // max
        $this->assertResult(
            '<span>
                 <bdo ac:core="yes" dir="rtl">
                    #PCDATA <span>Inline</span>
                 </bdo>
             </span>'
        );
        
        // min
        $this->assertResult(
            '<bdo></bdo>', '<bdo dir="ltr"></bdo>'
        );
        
        // children
        $this->assertResult(
            '<bdo dir="rtl">Text<span></span><div></div></bdo>',
            '<bdo dir="rtl">Text<span></span></bdo>'
        );
        
        // global attr
        $this->assertResult(
            '<br dir="ltr" /><span dir="ltr"></span>',
            '<br /><span dir="ltr"></span>'
        );
        
    }
    
}

?>