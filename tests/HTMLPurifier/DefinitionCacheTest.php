<?php

require_once 'HTMLPurifier/DefinitionCache.php';

class HTMLPurifier_DefinitionCacheTest extends UnitTestCase
{
    function test_create() {
        $config = HTMLPurifier_Config::createDefault();
        $cache  = HTMLPurifier_DefinitionCache::create('Test', $config);
        $this->assertEqual($cache, new HTMLPurifier_DefinitionCache_Serializer('Test'));
    }
    
    function test_isOld() {
        $cache = new HTMLPurifier_DefinitionCache('Test'); // non-functional
        
        $config = HTMLPurifier_Config::createDefault();
        $config->version = '1.0.0';
        $config->revision = 10;
        
        $this->assertIdentical($cache->isOld('1.0.0-10-hashstuffhere', $config), false);
        $this->assertIdentical($cache->isOld('1.5.0-1-hashstuffhere', $config), false);
        
        $this->assertIdentical($cache->isOld('0.9.0-1-hashstuffhere', $config), true);
        $this->assertIdentical($cache->isOld('1.0.0-1-hashstuffhere', $config), true);
        $this->assertIdentical($cache->isOld('1.0.0beta-11-hashstuffhere', $config), true);
    }
    
}

?>