<?php

require_once 'HTMLPurifier/DefinitionCache.php';

class HTMLPurifier_DefinitionCacheTest extends UnitTestCase
{
    
    function test_isOld() {
        $cache = new HTMLPurifier_DefinitionCache('Test'); // non-functional
        
        $old_copy = HTMLPurifier_ConfigSchema::instance();
        $o = new HTMLPurifier_ConfigSchema();
        HTMLPurifier_ConfigSchema::instance($o);
        
        HTMLPurifier_ConfigSchema::defineNamespace('Test', 'Test namespace');
        HTMLPurifier_ConfigSchema::define('Test', 'DefinitionRev', 1, 'int', 'Definition revision.');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->version = '1.0.0';
        $config->set('Test', 'DefinitionRev', 10);
        
        $this->assertIdentical($cache->isOld('1.0.0-10-hashstuffhere', $config), false);
        $this->assertIdentical($cache->isOld('1.5.0-1-hashstuffhere', $config), false);
        
        $this->assertIdentical($cache->isOld('0.9.0-1-hashstuffhere', $config), true);
        $this->assertIdentical($cache->isOld('1.0.0-1-hashstuffhere', $config), true);
        $this->assertIdentical($cache->isOld('1.0.0beta-11-hashstuffhere', $config), true);
        
        HTMLPurifier_ConfigSchema::instance($old_copy);
        
    }
    
}

?>