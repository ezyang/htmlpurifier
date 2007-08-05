<?php

require_once 'HTMLPurifier/DefinitionCacheFactory.php';

class HTMLPurifier_DefinitionCacheFactoryTest extends HTMLPurifier_Harness
{
    
    var $newFactory;
    var $oldFactory;
    
    function setup() {
        $new = new HTMLPurifier_DefinitionCacheFactory();
        $this->oldFactory = HTMLPurifier_DefinitionCacheFactory::instance();
        HTMLPurifier_DefinitionCacheFactory::instance($new);
    }
    
    function teardown() {
        HTMLPurifier_DefinitionCacheFactory::instance($this->oldFactory);
    }
    
    function test_create() {
        $config  = HTMLPurifier_Config::createDefault();
        $factory = HTMLPurifier_DefinitionCacheFactory::instance();
        $cache   = $factory->create('Test', $config);
        $this->assertEqual($cache, new HTMLPurifier_DefinitionCache_Serializer('Test'));
    }
    
    function test_create_withDecorator() {
        $config  = HTMLPurifier_Config::createDefault();
        $factory =& HTMLPurifier_DefinitionCacheFactory::instance();
        $factory->addDecorator('Memory');
        $cache =& $factory->create('Test', $config);
        $cache_real = new HTMLPurifier_DefinitionCache_Decorator_Memory();
        $cache_real = $cache_real->decorate(new HTMLPurifier_DefinitionCache_Serializer('Test'));
        $this->assertEqual($cache, $cache_real);
    }
    
    function test_create_withDecoratorObject() {
        $config  = HTMLPurifier_Config::createDefault();
        $factory =& HTMLPurifier_DefinitionCacheFactory::instance();
        $factory->addDecorator(new HTMLPurifier_DefinitionCache_Decorator_Memory());
        $cache =& $factory->create('Test', $config);
        $cache_real = new HTMLPurifier_DefinitionCache_Decorator_Memory();
        $cache_real = $cache_real->decorate(new HTMLPurifier_DefinitionCache_Serializer('Test'));
        $this->assertEqual($cache, $cache_real);
    }
    
    function test_create_recycling() {
        $config  = HTMLPurifier_Config::createDefault();
        $factory =& HTMLPurifier_DefinitionCacheFactory::instance();
        $cache =& $factory->create('Test', $config);
        $cache2 =& $factory->create('Test', $config);
        $this->assertReference($cache, $cache2);
    }
    
    function test_null() {
        $config = HTMLPurifier_Config::create(array('Core.DefinitionCache' => null));
        $factory =& HTMLPurifier_DefinitionCacheFactory::instance();
        $cache =& $factory->create('Test', $config);
        $this->assertEqual($cache, new HTMLPurifier_DefinitionCache_Null('Test'));
    }
    
}

