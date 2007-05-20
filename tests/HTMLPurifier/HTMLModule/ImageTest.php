<?php

require_once 'HTMLPurifier/HTMLModuleHarness.php';

class HTMLPurifier_HTMLModule_ImageTest extends HTMLPurifier_HTMLModuleHarness
{
    
    function test() {
        
        $this->setupScaffold('Image');
        
        // max
        $this->assertResult(
            '<span>
                 <img
                    src="example.png"
                    alt="Example image"
                    longdesc="example.description.txt"
                    height="42"
                    width="42"
                    ac:common="yes"
                 />
             </span>'
        );
        
        // required attributes
        $this->assertResult(
            '<img src="foo.png" />',
            '<img src="foo.png" alt="foo.png" />'
        );
        
        // empty
        $this->assertResult(
            '<img src="foo.png" alt="foo">',
            '<img src="foo.png" alt="foo" />'
        );
        
        // unsupported attributes
        $this->assertResult(
            '<img
                src="example.png"
                alt="Example"
                usemap="#foo"
                ismap="ismap"
             />',
             '<img src="example.png" alt="Example" />'
        );
        
    }
    
}

?>