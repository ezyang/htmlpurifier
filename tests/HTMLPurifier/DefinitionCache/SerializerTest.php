<?php

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

class HTMLPurifier_DefinitionCache_SerializerTest extends UnitTestCase
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
        if($file) unlink($file); // prevent previous failures from causing problems
        
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
        
        $cache->remove($config);
        $this->assertFileNotExist($file);
        
        $def_4 = $cache->get($config);
        $this->assertFalse($def_4);
        
    }
    
    function test_errors() {
        /*$cache = new HTMLPurifier_DefinitionCache_Serializer('Test');
        $def = new HTMLPurifier_Definition();
        $def->setup = true;
        $def->type = 'NotTest';
        
        $this->expectError('Cannot add definition of type NotTest to cache for Test');*/
    }
    
    function test_flush() {
        /*
        $cache = new HTMLPurifier_DefinitionCache_Serializer();
        
        $config1 = $this->generateConfigMock(array('Candles' => 1));
        $config2 = $this->generateConfigMock(array('Candles' => 2));
        $config3 = $this->generateConfigMock(array('Candles' => 3));
        
        $def1 = $this->generateDefinition(array('info_candles' => 1));
        $def2 = $this->generateDefinition(array('info_candles' => 2));
        $def3 = $this->generateDefinition(array('info_candles' => 3));
        
        $cache->add($def1, $config1);
        $cache->add($def2, $config2);
        $cache->add($def3, $config3);
        
        $this->assertTrue($cache->get('Test', $config1));
        $this->assertTrue($cache->get('Test', $config2));
        $this->assertTrue($cache->get('Test', $config3));
        
        $cache->flush('Test');
        */
    }
    
    /**
     * Generate a configuration mock object that returns $values
     * to a getBatch() call
     * @param $values Values to return when getBatch is invoked
     */
    function generateConfigMock($values) {
        generate_mock_once('HTMLPurifier_Config');
        $config = new HTMLPurifier_ConfigMock($this);
        $config->setReturnValue('getBatch', $values, array('Test'));
        return $config;
    }
    
    /**
     * Returns an anonymous def that has been setup and named Test
     */
    function generateDefinition($member_vars = array()) {
        $def = new HTMLPurifier_Definition();
        $def->setup = true;
        $def->type  = 'Test';
        foreach ($member_vars as $key => $val) {
            $def->$key = $val;
        }
        return $def;
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