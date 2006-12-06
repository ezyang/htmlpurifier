<?php

require_once 'HTMLPurifier/Token.php';

class HTMLPurifier_TokenTest extends UnitTestCase
{
    
    function assertTokenConstruction($name, $attr,
        $expect_name = null, $expect_attr = null
    ) {
        if ($expect_name === null) $expect_name = $name;
        if ($expect_attr === null) $expect_attr = $attr;
        $token = new HTMLPurifier_Token_Start($name, $attr);
        
        $this->assertEqual($expect_name, $token->name);
        $this->assertEqual($expect_attr, $token->attr);
    }
    
    function testConstruct() {
        
        // standard case
        $this->assertTokenConstruction('a', array('href' => 'about:blank'));
        
        // lowercase the tag's name
        $this->assertTokenConstruction('A', array('href' => 'about:blank'),
                                       'a');
        
        // lowercase attributes
        $this->assertTokenConstruction('a', array('HREF' => 'about:blank'),
                                       'a', array('href' => 'about:blank'));
        
    }
    
}

?>