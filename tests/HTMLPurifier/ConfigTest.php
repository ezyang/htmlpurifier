<?php

require_once 'HTMLPurifier/Config.php';

class HTMLPurifier_ConfigTest extends UnitTestCase
{
    
    var $our_copy, $old_copy;
    
    function setUp() {
        $our_copy = new HTMLPurifier_ConfigDef();
        $this->old_copy = HTMLPurifier_ConfigDef::instance();
        $this->our_copy =& HTMLPurifier_ConfigDef::instance($our_copy);
    }
    
    function tearDown() {
        HTMLPurifier_ConfigDef::instance($this->old_copy);
    }
    
    function test() {
        
        HTMLPurifier_ConfigDef::defineNamespace('Core', 'Corestuff');
        HTMLPurifier_ConfigDef::defineNamespace('Attr', 'Attributes');
        HTMLPurifier_ConfigDef::defineNamespace('Extension', 'Extensible');
        
        HTMLPurifier_ConfigDef::define(
            'Core', 'Key', false, 'bool', 'A boolean directive.'
        );
        HTMLPurifier_ConfigDef::define(
            'Attr', 'Key', 42, 'int', 'An integer directive.'
        );
        HTMLPurifier_ConfigDef::define(
            'Extension', 'Pert', 'foo', 'string', 'A string directive.'
        );
        
        HTMLPurifier_ConfigDef::defineAllowedValues(
            'Extension', 'Pert', array('foo', 'moo')
        );
        HTMLPurifier_ConfigDef::defineValueAliases(
            'Extension', 'Pert', array('cow' => 'moo')
        );
        
        $config = HTMLPurifier_Config::createDefault();
        
        // test default value retrieval
        $this->assertIdentical($config->get('Core', 'Key'), false);
        $this->assertIdentical($config->get('Attr', 'Key'), 42);
        $this->assertIdentical($config->get('Extension', 'Pert'), 'foo');
        
        // set some values
        $config->set('Core', 'Key', true);
        $this->assertIdentical($config->get('Core', 'Key'), true);
        
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
        
        // try to set not allowed value
        $config->set('Extension', 'Pert', 'wizard');
        $this->assertError('Value not supported');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        // try to set not allowed value
        $config->set('Extension', 'Pert', 34);
        $this->assertError('Value is of invalid type');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        // set aliased value
        $config->set('Extension', 'Pert', 'cow');
        $this->assertNoErrors();
        $this->assertIdentical($config->get('Extension', 'Pert'), 'moo');
        
    }
    
}

?>