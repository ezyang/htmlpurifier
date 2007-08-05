<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/CSS/FontFamily.php';

class HTMLPurifier_AttrDef_CSS_FontFamilyTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_CSS_FontFamily();
        
        $this->assertDef('Gill, Helvetica, sans-serif');
        $this->assertDef('\'Times New Roman\', serif');
        $this->assertDef('"Times New Roman"', "'Times New Roman'");
        $this->assertDef('01234');
        $this->assertDef(',', false);
        $this->assertDef('Times New Roman, serif', '\'Times New Roman\', serif');
        $this->assertDef($d = "'John\\'s Font'");
        $this->assertDef("John's Font", $d);
        $this->assertDef($d = "'\xE5\xAE\x8B\xE4\xBD\x93'");
        $this->assertDef("\xE5\xAE\x8B\xE4\xBD\x93", $d);
        
    }
    
}

