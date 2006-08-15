<?php

require_once 'HTMLPurifier/TokenFactory.php';

class HTMLPurifier_TokenFactoryTest extends UnitTestCase
{
    public function test() {
        
        $factory = new HTMLPurifier_TokenFactory();
        
        $regular = new HTMLPurifier_Token_Start('a', array('href' => 'about:blank'));
        $generated = $factory->createStart('a', array('href' => 'about:blank'));
        
        $this->assertEqual($regular, $generated);
        
    }
}

?>