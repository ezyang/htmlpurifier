<?php

class ConfigSchema_StringHashTest extends UnitTestCase
{
    
    public function testUsed() {
        $hash = new ConfigSchema_StringHash(array(
            'key' => 'value',
            'key2' => 'value2'
        ));
        $this->assertIdentical($hash->getAccessed(), array());
        $t = $hash['key'];
        $this->assertIdentical($hash->getAccessed(), array('key' => true));
        $hash->resetAccessed();
        $this->assertIdentical($hash->getAccessed(), array());
    }
    
}
