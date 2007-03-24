<?php

require_once 'HTMLPurifier/AttrDef/CSS/Border.php';

class HTMLPurifier_AttrDef_CSS_BorderTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $config = HTMLPurifier_Config::createDefault();
        $this->def = new HTMLPurifier_AttrDef_CSS_Border($config);
        
        $this->assertDef('thick solid red', 'thick solid #F00');
        $this->assertDef('thick solid');
        $this->assertDef('solid red', 'solid #F00');
        $this->assertDef('1px solid #000');
        
    }
    
}

?>