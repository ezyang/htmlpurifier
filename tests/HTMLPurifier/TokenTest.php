<?php

require_once 'HTMLPurifier/Token.php';

class HTMLPurifier_TokenTest extends UnitTestCase
{
    
    function assertTokenConstruction($name, $attributes,
        $expect_name = null, $expect_attributes = null
    ) {
        if ($expect_name === null) $expect_name = $name;
        if ($expect_attributes === null) $expect_attributes = $attributes;
        $token = new HTMLPurifier_Token_Start($name, $attributes);
        
        $this->assertEqual($expect_name,       $token->name);
        $this->assertEqual($expect_attributes, $token->attributes);
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