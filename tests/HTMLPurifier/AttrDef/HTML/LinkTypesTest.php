<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/HTML/LinkTypes.php';

class HTMLPurifier_AttrDef_HTML_LinkTypesTest extends HTMLPurifier_AttrDefHarness
{
    
    function testNull() {
        
        $this->def = new HTMLPurifier_AttrDef_HTML_LinkTypes('rel');
        $this->config->set('Attr', 'AllowedRel', array('nofollow', 'foo'));
        
        $this->assertDef('', false);
        $this->assertDef('nofollow', true);
        $this->assertDef('nofollow foo', true);
        $this->assertDef('nofollow bar', 'nofollow');
        $this->assertDef('bar', false);
        
    }
    
}

