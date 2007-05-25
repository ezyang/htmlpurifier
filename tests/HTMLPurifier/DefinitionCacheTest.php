<?php

require_once 'HTMLPurifier/DefinitionCache.php';

class HTMLPurifier_DefinitionCacheTest extends UnitTestCase
{
    function test_create() {
        $config = HTMLPurifier_Config::createDefault();
        $cache  = HTMLPurifier_DefinitionCache::create('Test', $config);
        $this->assertEqual($cache, new HTMLPurifier_DefinitionCache_Serializer('Test'));
    }
}

?>