<?php

require_once 'HTMLPurifier/DefinitionCacheHarness.php';
require_once 'HTMLPurifier/DefinitionCache/Decorator/Memory.php';

generate_mock_once('HTMLPurifier_DefinitionCache');

class HTMLPurifier_DefinitionCache_DecoratorHarness extends HTMLPurifier_DefinitionCacheHarness
{
    
    function setup() {
        $this->mock     = new HTMLPurifier_DefinitionCacheMock();
        $this->mock->type = 'Test';
        $this->cache    = $this->cache->decorate($this->mock);
        $this->def      = $this->generateDefinition();
        $this->config   = $this->generateConfigMock();
    }
    
    function teardown() {
        unset($this->mock);
        unset($this->cache);
    }
    
}

