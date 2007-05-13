<?php

require_once 'HTMLPurifier/HTMLModuleHarness.php';

class HTMLPurifier_HTMLModule_BdoTest extends HTMLPurifier_HTMLModuleHarness
{
    
    function test() {
        
        // max
        $this->assertResult(
            '<span>
                 <bdo
                    id="test-id"
                    class="class-name"
                    style="font-weight:bold;"
                    title="Title of tag"
                    lang="en"
                    xml:lang="en"
                    dir="rtl"
                 >
                    #PCDATA <span>Inline</span>
                 </bdo>
             </span>', true, array('Attr.EnableID' => true)
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