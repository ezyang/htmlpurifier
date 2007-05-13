<?php

require_once 'HTMLPurifier/HTMLModuleHarness.php';

class HTMLPurifier_HTMLModule_EditTest extends HTMLPurifier_HTMLModuleHarness
{
    
    function test() {
        
        // max
        $this->assertResult(
            '<span>
                 <ins cite="http://www.example.com/">
                    #PCDATA <span></span>
                 </ins>
                 <del cite="http://www.example.com/">
                    #PCDATA <span></span>
                 </del>
             </span>
             <div>
                 <ins cite="http://www.example.com/">
                    #PCDATA <div></div> <span></span>
                 </ins>
                 <del cite="http://www.example.com/">
                    #PCDATA <div></div> <span></span>
                 </del>
             </div>'
        );
        
        // inline removal
        $this->assertResult(
            '<span>
                 <ins><div></div></ins>
                 <del><div></div></del>
             </span>',
             '<span>
                 <ins></ins>
                 <del></del>
             </span>'
        );
        
        // unsupported attributes
        $this->assertResult(
            '<ins
                datetime="1994-11-05T13:15:30Z"
            ></ins>
            <del
                datetime="1994-11-05T13:15:30Z"
            ></del>',
            '<ins></ins>
            <del></del>'
        );
        
    }
    
}

?>