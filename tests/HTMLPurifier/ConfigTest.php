<?php

require_once 'HTMLPurifier/Config.php';

class HTMLPurifier_ConfigTest extends UnitTestCase
{
    
    function test() {
        
        $def = new HTMLPurifier_ConfigDef();
        $def->info = array(
            'Core' => array('Key' => false),
            'Attr' => array('Key' => 42),
            'Extension' => array('Pert' => 'moo')
        );
        
        $config = new HTMLPurifier_Config($def);
        
        // test default value retrieval
        $this->assertIdentical($config->get('Core', 'Key'), false);
        $this->assertIdentical($config->get('Attr', 'Key'), 42);
        $this->assertIdentical($config->get('Extension', 'Pert'), 'moo');
        
        // set some values
        $config->set('Core', 'Key', 'foobar');
        $this->assertIdentical($config->get('Core', 'Key'), 'foobar');
        
        // try to retrieve undefined value
        $config->get('Core', 'NotDefined');
        $this->assertError('Cannot retrieve value of undefined directive');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        // try to set undefined value
        $config->set('Foobar', 'Key', 'foobar');
        $this->assertError('Cannot set undefined directive to value');
        $this->assertNoErrors();
        $this->swallowErrors();
        
    }
    
}

?>