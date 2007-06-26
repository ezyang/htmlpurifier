<?php

require_once 'HTMLPurifier/ErrorCollector.php';
require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_ErrorsHarness extends UnitTestCase
{
    
    var $config, $context;
    var $collector, $generator;
    
    function setup() {
        $this->config = HTMLPurifier_Config::create(array('Core.CollectErrors' => true));
        $this->context = new HTMLPurifier_Context();
        generate_mock_once('HTMLPurifier_ErrorCollector');
        $this->collector = new HTMLPurifier_ErrorCollectorMock($this);
        $this->context->register('ErrorCollector', $this->collector);
    }
    
    function expectErrorCollection() {
        $args = func_get_args();
        $this->collector->expectOnce('send', $args);
    }
    
}

?>