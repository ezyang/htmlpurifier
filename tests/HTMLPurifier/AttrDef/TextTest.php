<?php

require_once 'HTMLPurifier/AttrDef/Text.php';

class HTMLPurifier_AttrDef_TextTest extends UnitTestCase
{
    
    function test() {
        
        $def = new HTMLPurifier_AttrDef_Text();
        
        $this->assertTrue($def->validate('This is spiffy text!'));
        $this->assertEqual('Casual CDATA parsecheck.',
                           $def->validate(" Casual\tCDATA parse\ncheck. "));
        
    }
    
}

?>