<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/Background.php';

class HTMLPurifier_AttrDef_BackgroundTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_Background(HTMLPurifier_Config::createDefault());
        
        $valid = '#333 url(chess.png) repeat fixed 50% top';
        $this->assertDef($valid);
        $this->assertDef('url("chess.png") #333 50% top repeat fixed', $valid);
        
    }
    
}

?>