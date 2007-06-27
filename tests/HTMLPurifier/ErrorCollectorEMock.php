<?php

require_once 'HTMLPurifier/ErrorCollector.php';

generate_mock_once('HTMLPurifier_ErrorCollector');

/**
 * Extended error collector mock that has the ability to expect context
 */
class HTMLPurifier_ErrorCollectorEMock extends HTMLPurifier_ErrorCollectorMock
{
    
    var $_context;
    var $_expected_context = array();
    var $_expected_context_at = array();
    
    function prepare(&$context) {
        $this->_context =& $context;
    }
    
    function expectContext($key, $value) {
        $this->_expected_context[$key] = $value;
    }
    function expectContextAt($step, $key, $value) {
        $this->_expected_context_at[$step][$key] = $value;
    }
    
    function send($severity, $msg) {
        // test for context
        $test = &$this->_getCurrentTestCase();
        foreach ($this->_expected_context as $key => $value) {
            $test->assertEqual($value, $this->_context->get($key));
        }
        $step = $this->getCallCount('send');
        if (isset($this->_expected_context_at[$step])) {
            foreach ($this->_expected_context_at[$step] as $key => $value) {
                $test->assertEqual($value, $this->_context->get($key));
            }
        }
        // boilerplate mock code, does not have return value or references
        $args = func_get_args();
        $this->_invoke('send', $args);
    }
    
}

