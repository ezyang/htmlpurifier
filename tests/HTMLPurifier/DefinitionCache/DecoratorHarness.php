<?php

generate_mock_once('HTMLPurifier_DefinitionCache');

class HTMLPurifier_DefinitionCache_DecoratorHarness extends HTMLPurifier_DefinitionCacheHarness
{

    public $cache;

    public $mock;

    public $def;

    public function setup()
    {
        $this->mock     = new HTMLPurifier_DefinitionCacheMock();
        $this->mock->type = 'Test';
        $this->cache    = $this->cache->decorate($this->mock);
        $this->def      = $this->generateDefinition();
        $this->config   = $this->generateConfigMock();
    }

    public function teardown()
    {
        unset($this->mock);
        unset($this->cache);
    }

}

// vim: et sw=4 sts=4
