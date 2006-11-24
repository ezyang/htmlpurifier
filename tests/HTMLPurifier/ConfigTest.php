<?php

require_once 'HTMLPurifier/Config.php';

class HTMLPurifier_ConfigTest extends UnitTestCase
{
    
    var $our_copy, $old_copy;
    
    function setUp() {
        // set up a dummy schema object for testing
        $our_copy = new HTMLPurifier_ConfigSchema();
        $this->old_copy = HTMLPurifier_ConfigSchema::instance();
        $this->our_copy =& HTMLPurifier_ConfigSchema::instance($our_copy);
    }
    
    function tearDown() {
        HTMLPurifier_ConfigSchema::instance($this->old_copy);
    }
    
    function test() {
        
        HTMLPurifier_ConfigSchema::defineNamespace('Core', 'Corestuff');
        HTMLPurifier_ConfigSchema::defineNamespace('Attr', 'Attributes');
        HTMLPurifier_ConfigSchema::defineNamespace('Extension', 'Extensible');
        
        HTMLPurifier_ConfigSchema::define(
            'Core', 'Key', false, 'bool', 'A boolean directive.'
        );
        HTMLPurifier_ConfigSchema::define(
            'Attr', 'Key', 42, 'int', 'An integer directive.'
        );
        HTMLPurifier_ConfigSchema::define(
            'Extension', 'Pert', 'foo', 'string', 'A string directive.'
        );
        HTMLPurifier_ConfigSchema::define(
            'Core', 'Encoding', 'utf-8', 'istring', 'Case insensitivity!'
        );
        
        HTMLPurifier_ConfigSchema::define(
            'Extension', 'CanBeNull', null, 'string/null', 'Null or string!'
        );
        
        HTMLPurifier_ConfigSchema::defineAllowedValues(
            'Extension', 'Pert', array('foo', 'moo')
        );
        HTMLPurifier_ConfigSchema::defineValueAliases(
            'Extension', 'Pert', array('cow' => 'moo')
        );
        HTMLPurifier_ConfigSchema::defineAllowedValues(
            'Core', 'Encoding', array('utf-8', 'iso-8859-1')
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
        
        // try to set undefined value
        $config->set('Foobar', 'Key', 'foobar');
        $this->assertError('Cannot set undefined directive to value');
        $this->assertNoErrors();
        
        // try to set not allowed value
        $config->set('Extension', 'Pert', 'wizard');
        $this->assertError('Value not supported');
        $this->assertNoErrors();
        
        // try to set not allowed value
        $config->set('Extension', 'Pert', 34);
        $this->assertError('Value is of invalid type');
        $this->assertNoErrors();
        
        // set aliased value
        $config->set('Extension', 'Pert', 'cow');
        $this->assertNoErrors();
        $this->assertIdentical($config->get('Extension', 'Pert'), 'moo');
        
        // case-insensitive attempt to set value that is allowed
        $config->set('Core', 'Encoding', 'ISO-8859-1');
        $this->assertNoErrors();
        $this->assertIdentical($config->get('Core', 'Encoding'), 'iso-8859-1');
        
        // set null to directive that allows null
        $config->set('Extension', 'CanBeNull', null);
        $this->assertNoErrors();
        $this->assertIdentical($config->get('Extension', 'CanBeNull'), null);
        
        $config->set('Extension', 'CanBeNull', 'foobar');
        $this->assertNoErrors();
        $this->assertIdentical($config->get('Extension', 'CanBeNull'), 'foobar');
        
        // set null to directive that doesn't allow null
        $config->set('Extension', 'Pert', null);
        $this->assertError('Value is of invalid type');
        $this->assertNoErrors();
        
        // grab a namespace
        $config->set('Attr', 'Key', 0xBEEF);
        $this->assertIdentical(
            $config->getBatch('Attr'),
            array(
                'Key' => 0xBEEF
            )
        );
        
        // grab a non-existant namespace
        $config->getBatch('FurnishedGoods');
        $this->assertError('Cannot retrieve undefined namespace');
        $this->assertNoErrors();
        
    }
    
    function test_getDefinition() {
        
        // we actually want to use the old copy, because the definition
        // generation routines have dependencies on configuration values
        
        $this->old_copy = HTMLPurifier_ConfigSchema::instance($this->old_copy);
        
        $config = HTMLPurifier_Config::createDefault();
        $def = $config->getHTMLDefinition();
        $this->assertIsA($def, 'HTMLPurifier_HTMLDefinition');
        
        $def = $config->getCSSDefinition();
        $this->assertIsA($def, 'HTMLPurifier_CSSDefinition');
        
    }
    
    function test_loadArray() {
        // setup a few dummy namespaces/directives for our testing
        HTMLPurifier_ConfigSchema::defineNamespace('Zoo', 'Animals we have.');
        HTMLPurifier_ConfigSchema::define('Zoo', 'Aadvark', 0, 'int', 'Have?');
        HTMLPurifier_ConfigSchema::define('Zoo', 'Boar',    0, 'int', 'Have?');
        HTMLPurifier_ConfigSchema::define('Zoo', 'Camel',   0, 'int', 'Have?');
        HTMLPurifier_ConfigSchema::define(
            'Zoo', 'Others', array(), 'list', 'Other animals we have one of.'
        );
        
        $config_manual = HTMLPurifier_Config::createDefault();
        $config_loadabbr = HTMLPurifier_Config::createDefault();
        $config_loadfull = HTMLPurifier_Config::createDefault();
        
        $config_manual->set('Zoo', 'Aadvark', 3);
        $config_manual->set('Zoo', 'Boar', 5);
        $config_manual->set('Zoo', 'Camel', 2000); // that's a lotta camels!
        $config_manual->set('Zoo', 'Others', array('Peacock', 'Dodo')); // wtf!
        
        // condensed form
        $config_loadabbr->loadArray(array(
            'Zoo.Aadvark' => 3,
            'Zoo.Boar' => 5,
            'Zoo.Camel' => 2000,
            'Zoo.Others' => array('Peacock', 'Dodo')
        ));
        
        // fully expanded form
        $config_loadfull->loadArray(array(
            'Zoo' => array(
                'Aadvark' => 3,
                'Boar' => 5,
                'Camel' => 2000,
                'Others' => array('Peacock', 'Dodo')
            )
        ));
        
        $this->assertEqual($config_manual, $config_loadabbr);
        $this->assertEqual($config_manual, $config_loadfull);
        
    }
    
}

?>