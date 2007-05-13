<?php

require_once 'HTMLPurifier/HTMLModuleHarness.php';

class HTMLPurifier_HTMLModule_HypertextTest extends HTMLPurifier_HTMLModuleHarness
{
    
    function test() {
        
        // max
        $this->assertResult(
            '<span>
                 <a
                    href="http://www.example.com/"
                    rel="nofollow"
                    rev="index"
                 >
                    #PCDATA <span>Inline</span>
                 </a>
             </span>', true, array(
                'Attr.AllowedRel' => 'nofollow',
                'Attr.AllowedRev' => 'index'
             )
        );
        
        // invalid children
        $this->assertResult(
            '<a>Text<span><a></a></span><div></div><a></a></a>',
            '<a>Text<span></span></a>'
        );
        
        // unsupported attributes
        $this->assertResult(
            '<a
                charset="utf-8"
                type="text/html"
                hreflang="en"
                accesskey="f"
                shape="rect"
                coords="0,0,20,0"
                tabindex="3"
                onfocus="foo();"
                onblur="bar();"
             ></a>',
             '<a></a>'
        );
        
    }
    
}

?>