<?php

require_once 'HTMLPurifier/HTMLModuleHarness.php';

class HTMLPurifier_HTMLModule_BdoTest extends HTMLPurifier_HTMLModuleHarness
{
    
    function test() {
        
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
        
    }
    
}

?>