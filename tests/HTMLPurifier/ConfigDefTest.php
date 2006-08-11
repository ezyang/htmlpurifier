<?php

require_once 'HTMLPurifier/ConfigDef.php';

class HTMLPurifier_ConfigDefTest extends UnitTestCase
{
    
    var $old_copy;
    var $our_copy;
    
    function setUp() {
        // yes, I know this is slightly convoluted, but that's the price
        // you pay for using Singletons. Good thing we can overload it.
        
        // first, let's get a clean copy to do tests
        $our_copy = new HTMLPurifier_ConfigDef();
        // get the old copy
        $this->old_copy = HTMLPurifier_ConfigDef::instance();
        // put in our copy, and reassign to the REAL reference
        $this->our_copy =& HTMLPurifier_ConfigDef::instance($our_copy);
    }
    
    function tearDown() {
        // testing is done, restore the old copy
        HTMLPurifier_ConfigDef::instance($this->old_copy);
    }
    
    function testNormal() {
        
        HTMLPurifier_ConfigDef::defineNamespace('Core', 'Configuration that '.
            'is always available.');
        $this->assertIdentical( array(
                'Core' => array()
            ), $this->our_copy->info);
        
        // note that the description is silently dropped
        HTMLPurifier_ConfigDef::define('Core', 'Name', 'default value',
            'This is a description of the directive.');
        $this->assertIdentical( array(
                'Core' => array(
                    'Name' => 'default value'
                )
            ), $this->our_copy->info);
        
        // test an invalid namespace
        HTMLPurifier_ConfigDef::define('Extension', 'Name', false, 'This is '.
            'for an extension, but we have not defined its namespace!');
        $this->assertError('Cannot define directive for undefined namespace');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        // test overloading already defined value
        HTMLPurifier_ConfigDef::define('Core', 'Name', 89,
            'What, you\'re not allowed to overload directives? Bummer!');
        $this->assertError('Cannot redefine directive');
        $this->assertNoErrors();
        $this->swallowErrors();
        
    }
    
}

?>