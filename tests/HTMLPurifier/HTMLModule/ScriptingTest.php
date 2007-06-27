<?php

require_once 'HTMLPurifier/HTMLModuleHarness.php';

class HTMLPurifier_HTMLModule_ScriptingTest extends HTMLPurifier_HTMLModuleHarness
{
    
    function test() {
        
        // default (remove everything)
        $this->assertResult(
            '<script type="text/javascript">foo();</script>', ''
        );
        
        // enabled
        $this->assertResult(
            '<script type="text/javascript">foo();</script>', true,
            array('HTML.Trusted' => true)
        );
        
        // CDATA
        $this->assertResult(
'//<![CDATA[
alert("<This is compatible with XHTML>");
//]]> ', true,
            array('HTML.Trusted' => true)
        );
        
        // max
        $this->assertResult(
            '<script
                defer="defer"
                src="test.js"
                type="text/javascript"
            >PCDATA</script>', true,
            array('HTML.Trusted' => true, 'Core.CommentScriptContents' => false)
        );
        
        // unsupported
        $this->assertResult(
            '<script
                type="text/javascript"
                charset="utf-8"
            >PCDATA</script>',
            '<script type="text/javascript">PCDATA</script>',
            array('HTML.Trusted' => true, 'Core.CommentScriptContents' => false)
        );
        
    }
    
}

