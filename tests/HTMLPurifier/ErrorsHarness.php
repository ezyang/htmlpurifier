<?php

require_once 'HTMLPurifier/ErrorCollectorEMock.php';
require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_ErrorsHarness extends HTMLPurifier_Harness
{
    
    var $config, $context;
    var $collector, $generator;
    
    function setup() {
        $this->config = HTMLPurifier_Config::create(array('Core.CollectErrors' => true));
        $this->context = new HTMLPurifier_Context();
        generate_mock_once('HTMLPurifier_ErrorCollector');
        $this->collector = new HTMLPurifier_ErrorCollectorEMock();
        $this->collector->prepare($this->context);
        $this->context->register('ErrorCollector', $this->collector);
    }
    
    function expectErrorCollection() {
        $args = func_get_args();
        $this->collector->expectOnce('send', $args);
    }
    
    function expectContext($key, $value) {
        $this->collector->expectContext($key, $value);
    }
    
}

