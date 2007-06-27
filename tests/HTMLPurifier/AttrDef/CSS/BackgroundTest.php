<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/CSS/Background.php';

class HTMLPurifier_AttrDef_CSS_BackgroundTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $config = HTMLPurifier_Config::createDefault();
        $this->def = new HTMLPurifier_AttrDef_CSS_Background($config);
        
        $valid = '#333 url(chess.png) repeat fixed 50% top';
        $this->assertDef($valid);
        $this->assertDef('url("chess.png") #333 50% top repeat fixed', $valid);
        
    }
    
}

