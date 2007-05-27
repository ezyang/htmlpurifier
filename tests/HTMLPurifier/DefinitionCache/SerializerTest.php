<?php

require_once 'HTMLPurifier/DefinitionCacheHarness.php';
require_once 'HTMLPurifier/DefinitionCache/Serializer.php';

class HTMLPurifier_Definition_SerializerMock extends HTMLPurifier_Definition
{
    
    var $_test;
    var $_expect = false;
    
    function HTMLPurifier_Definition_SerializerMock(&$test_case) {
        $this->_test =& $test_case;
    }
    
    function expectDoSetupOnce() {$this->_expect = true;}
    
    function doSetup($config) {
        if ($this->_expect) {
            $this->_test->pass();
        } else {
            $this->_test->fail('Unexpected call to doSetup');
        }
        unset($this->_test, $this->_expect);
    }
    
}

class HTMLPurifier_DefinitionCache_SerializerTest extends HTMLPurifier_DefinitionCacheHarness
{
    
    function test__SerializerMock_pass() {
        $config = 'config';
        generate_mock_once('UnitTestCase');
        $test =& new UnitTestCaseMock($this);
        $test->expectOnce('pass');
        $mock = new HTMLPurifier_Definition_SerializerMock($test);
        $mock->expectDoSetupOnce();
        $mock->doSetup($config);
    }
    
    function test__SerializerMock_fail() {
        $config = 'config';
        generate_mock_once('UnitTestCase');
        $test =& new UnitTestCaseMock($this);
        $test->expectOnce('fail');
        $mock = new HTMLPurifier_Definition_SerializerMock($test);
        $mock->doSetup($config);
    }
    
    function test() {
        
        $cache = new HTMLPurifier_DefinitionCache_Serializer('Test');
        
        $config_array = array('Foo' => 'Bar');
        $config_md5   = md5(serialize($config_array));
        
        $file = realpath(
            $rel_file = dirname(__FILE__) .
            '/../../../library/HTMLPurifier/DefinitionCache/Serializer/Test/' .
            $config_md5 . '.ser'
        );
        if($file && file_exists($file)) unlink($file); // prevent previous failures from causing problems
        
        $config = $this->generateConfigMock($config_array);
        $this->assertIdentical($config_md5, $cache->generateKey($config));
        
        $def_original = $this->generateDefinition();
        
        $cache->add($def_original, $config);
        $this->assertFileExist($rel_file);
        
        $file_generated = $cache->generateFilePath($config);
        $this->assertIdentical(realpath($rel_file), realpath($file_generated));
        
        $def_1 = $cache->get($config);
        $this->assertIdentical($def_original, $def_1);
        
        $def_original->info_random = 'changed';
        
        $cache->set($def_original, $config);
        $def_2 = $cache->get($config);
        
        $this->assertIdentical($def_original, $def_2);
        $this->assertNotEqual ($def_original, $def_1);
        
        $def_original->info_random = 'did it change?';
        
        $this->assertFalse($cache->add($def_original, $config));
        $def_3 = $cache->get($config);
        
        $this->assertNotEqual ($def_original, $def_3); // did not change!
        $this->assertIdentical($def_3, $def_2);
        
        $cache->replace($def_original, $config);
        $def_4 = $cache->get($config);
        $this->assertIdentical($def_original, $def_4);
        
        $cache->remove($config);
        $this->assertFileNotExist($file);
        
        $this->assertFalse($cache->replace($def_original, $config));
        $def_5 = $cache->get($config);
        $this->assertFalse($def_5);
        
    }
    
    function test_errors() {
        $cache = new HTMLPurifier_DefinitionCache_Serializer('Test');
        $def = new HTMLPurifier_Definition();
        $def->setup = true;
        $def->type = 'NotTest';
        $config = $this->generateConfigMock(array('Test' => 'foo'));
        
        $this->expectError('Cannot use definition of type NotTest in cache for Test');
        $cache->add($def, $config);
        
        $this->expectError('Cannot use definition of type NotTest in cache for Test');
        $cache->set($def, $config);
        
        $this->expectError('Cannot use definition of type NotTest in cache for Test');
        $cache->replace($def, $config);
    }
    
    function test_flush() {
        
        $cache = new HTMLPurifier_DefinitionCache_Serializer('Test');
        
        $config1 = $this->generateConfigMock(array('Test' => 1));
        $config2 = $this->generateConfigMock(array('Test' => 2));
        $config3 = $this->generateConfigMock(array('Test' => 3));
        
        $def1 = $this->generateDefinition(array('info_candles' => 1));
        $def2 = $this->generateDefinition(array('info_candles' => 2));
        $def3 = $this->generateDefinition(array('info_candles' => 3));
        
        $cache->add($def1, $config1);
        $cache->add($def2, $config2);
        $cache->add($def3, $config3);
        
        $this->assertEqual($def1, $cache->get($config1));
        $this->assertEqual($def2, $cache->get($config2));
        $this->assertEqual($def3, $cache->get($config3));
        
        $cache->flush();
        
        $this->assertFalse($cache->get($config1));
        $this->assertFalse($cache->get($config2));
        $this->assertFalse($cache->get($config3));
        
    }
    
    /**
     * Asserts that a file exists, ignoring the stat cache
     */
    function assertFileExist($file) {
        clearstatcache();
        $this->assertTrue(file_exists($file), 'Expected ' . $file . ' exists');
    }
    
    /**
     * Asserts that a file does not exist, ignoring the stat cache
     */
    function assertFileNotExist($file) {
        $this->assertFalse(file_exists($file), 'Expected ' . $file . ' does not exist');
    }
    
}

?>