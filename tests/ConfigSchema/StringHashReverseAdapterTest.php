<?php

class ConfigSchema_StringHashReverseAdapterTest extends UnitTestCase
{
    
    function makeSchema() {
        $schema = new HTMLPurifier_ConfigSchema();
        $schema->addNamespace('Ns', 'Description of ns.');
        $schema->addNamespace('Ns2', 'Description of ns2.');
        $schema->add('Ns', 'Dir', 'dairy', 'string',
                     "Description of default.\n");
        $schema->addAllowedValues('Ns', 'Dir', array('dairy', 'meat'));
        $schema->addValueAliases('Ns', 'Dir', array('milk' => 'dairy', 'cheese' => 'dairy'));
        $schema->addAlias('Ns', 'Dir2', 'Ns',  'Dir');
        $schema->addAlias('Ns2', 'Dir', 'Ns', 'Dir');
        return $schema;
    }
    
    function testNamespace() {
        $adapter = new ConfigSchema_StringHashReverseAdapter($this->makeSchema());
        $result = $adapter->get('Ns');
        $expect = array(
            'ID' => 'Ns',
            'DESCRIPTION' => "Description of ns.",
        );
        $this->assertIdentical($result, $expect);
    }
    
    function testBadNamespace() {
        $adapter = new ConfigSchema_StringHashReverseAdapter($this->makeSchema());
        $this->expectError("Namespace 'BadNs' doesn't exist in schema");
        $adapter->get('BadNs');
    }
    
    function testDirective() {
        
        $adapter = new ConfigSchema_StringHashReverseAdapter($this->makeSchema());
        
        $result = $adapter->get('Ns', 'Dir');
        $expect = array(
            'ID' => 'Ns.Dir',
            'TYPE' => 'string',
            'DEFAULT' => "'dairy'",
            'DESCRIPTION' => "Description of default.\n",
            'ALLOWED' => "'dairy', 'meat'",
            'VALUE-ALIASES' => "'milk' => 'dairy',\n'cheese' => 'dairy',\n",
            'ALIASES' => "Ns.Dir2, Ns2.Dir",
        );
        
        $this->assertIdentical($result, $expect);
        
    }
    
    function testBadDirective() {
        $adapter = new ConfigSchema_StringHashReverseAdapter($this->makeSchema());
        $this->expectError("Directive 'BadNs.BadDir' doesn't exist in schema");
        $adapter->get('BadNs', 'BadDir');
    }
    
    function assertMethod($func, $input, $expect) {
        $adapter = new ConfigSchema_StringHashReverseAdapter($this->makeSchema());
        $result = $adapter->$func($input);
        $this->assertIdentical($result, $expect);
    }
    
    function testExportEmptyHash() {
        $this->assertMethod('exportHash', array(), '');
    }
    
    function testExportHash() {
        $this->assertMethod('exportHash', array('foo' => 'bar'), "'foo' => 'bar',\n");
    }
    
    function testExportEmptyLookup() {
        $this->assertMethod('exportLookup', array(), '');
    }
    
    function testExportSingleLookup() {
        $this->assertMethod('exportLookup', array('key' => true), "'key'");
    }
    
    function testExportLookup() {
        $this->assertMethod('exportLookup', array('key' => true, 'key2' => true, 3 => true), "'key', 'key2', 3");
    }
    
    function assertExtraction($desc, $expect_desc, $expect_version) {
        
    }
    
}
