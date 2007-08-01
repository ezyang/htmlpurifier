<?php

class HTMLPurifier_DefinitionCacheHarness extends HTMLPurifier_Harness
{
    
    /**
     * Generate a configuration mock object that returns $values
     * to a getBatch() call
     * @param $values Values to return when getBatch is invoked
     */
    function generateConfigMock($serial = 'defaultserial') {
        generate_mock_once('HTMLPurifier_Config');
        $config = new HTMLPurifier_ConfigMock();
        $config->setReturnValue('getBatchSerial', $serial, array('Test'));
        $config->version = '1.0.0';
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
    
}

