<?php

require_once 'HTMLPurifier/AttrDef.php';

class HTMLPurifier_AttrDefTest extends UnitTestCase
{
    
    function test_parseCDATA() {
        
        $def = new HTMLPurifier_AttrDef();
        
        $this->assertEqual('', $def->parseCDATA(''));
        $this->assertEqual('', $def->parseCDATA("\t\n\r \t\t"));
        $this->assertEqual('foo', $def->parseCDATA("\t\n\r foo\t\t"));
        $this->assertEqual('ignorelinefeeds', $def->parseCDATA("ignore\nline\nfeeds"));
        $this->assertEqual('translate to space', $def->parseCDATA("translate\rto\tspace"));
        
    }
    
}

?>