<?php

class ConfigSchema_StringHashAdapterTest extends UnitTestCase
{
    function __construct() {
        generate_mock_once('HTMLPurifier_ConfigSchema');
        parent::UnitTestCase();
    }
    
    function assertAdapt($input, $calls) {
        $schema = new HTMLPurifier_ConfigSchemaMock();
        foreach ($calls as $func => $params) {
            $schema->expectOnce($func, $params);
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
                'add' => array(
                    'Namespace', 'Directive', 'defaultbar', 'string',
                    "Description of default.\n"
                )
            )
        );
    }
}
