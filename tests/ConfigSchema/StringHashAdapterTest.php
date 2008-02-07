<?php

class ConfigSchema_StringHashAdapterTest extends UnitTestCase
{
    function __construct() {
        generate_mock_once('HTMLPurifier_ConfigSchema');
        parent::UnitTestCase();
    }
    
    function assertAdapt($input, $calls = array()) {
        $schema = new HTMLPurifier_ConfigSchemaMock();
        $called = array();
        foreach ($calls as $signature) {
            list($func, $params) = $signature;
            if (!isset($called[$func])) $called[$func] = 0;
            $schema->expectAt($called[$func]++, $func, $params);
        }
        $adapter = new ConfigSchema_StringHashAdapter();
        $adapter->adapt($input, $schema);
    }
    
    function testBasic() {
        $this->assertAdapt(
            array(
                'ID' => 'Namespace.Directive',
                'DEFAULT' => "'default' . 'bar'",
                'TYPE' => 'string',
                'DESCRIPTION' => "Description of default.\n",
            ),
            array(
                array('add', array(
                    'Namespace', 'Directive', 'defaultbar', 'string',
                    "Description of default.\n"
                )),
            )
        );
    }
    
    function testNamespace() {
        $this->assertAdapt(
            array(
                'ID' => 'Namespace',
                'DESCRIPTION' => 'Description of namespace',
            ),
            array(
                array('addNamespace', array('Namespace', 'Description of namespace')),
            )
        );
    }
    
    function testValueAliases() {
        $this->assertAdapt(
            array(
                'ID' => 'Ns.Dir',
                'VALUE-ALIASES' => "
                    'milk' => 'dairy',
                    'cheese' => 'dairy',
                ",
            ),
            array(
                array('addValueAliases', array('Ns', 'Dir', array('milk' => 'dairy', 'cheese' => 'dairy'))),
            )
        );
    }
    
    function testAllowedValues() {
        $this->assertAdapt(
            array(
                'ID' => 'Ns.Dir',
                'ALLOWED' => "'val1', 'val2'",
            ),
            array(
                array('addAllowedValues', array('Ns', 'Dir', array('val1', 'val2'))),
            )
        );
    }
    
    function testAlias() {
        $this->assertAdapt(
            array(
                'ID' => 'Ns.Dir',
                'ALIASES' => "Ns.Dir2, Ns2.Dir",
            ),
            array(
                array('addAlias', array('Ns',  'Dir2', 'Ns', 'Dir')),
                array('addAlias', array('Ns2', 'Dir',  'Ns', 'Dir')),
            )
        );
    }
    
    function testCombo() {
        $this->assertAdapt(
            array(
                'ID' => 'Ns.Dir',
                'DEFAULT' => "'val' . '1'",
                'TYPE' => 'string',
                'DESCRIPTION' => "Description of default.\n",
                'VALUE-ALIASES' => "
                    'milk' => 'val1',
                    'cheese' => 'val1',
                ",
                'ALLOWED' => "'val1', 'val2'",
                'ALIASES' => "Ns.Dir2, Ns2.Dir",
            ),
            array(
                array('add', array(
                    'Ns', 'Dir', 'val1', 'string',
                    "Description of default.\n"
                )),
                array('addAllowedValues', array('Ns', 'Dir', array('val1', 'val2'))),
                array('addValueAliases', array('Ns', 'Dir', array('milk' => 'val1', 'cheese' => 'val1'))),
                array('addAlias', array('Ns', 'Dir2', 'Ns', 'Dir')),
                array('addAlias', array('Ns2', 'Dir', 'Ns', 'Dir')),
            )
        );
    }
    
    function testMissingIdError() {
        $this->expectError('Missing key ID in string hash');
        $this->assertAdapt(array());
    }
    
    function testExtraError() {
        $this->expectError("String hash key 'FOOBAR' not used by adapter");
        $this->assertAdapt(
            array(
                'ID' => 'Namespace',
                'DESCRIPTION' => 'Description of namespace',
                'FOOBAR' => 'Extra stuff',
            ),
            array(
                array('addNamespace', array('Namespace', 'Description of namespace')),
            )
        );
    }
    
}
