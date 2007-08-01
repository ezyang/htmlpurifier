<?php

require_once 'HTMLPurifier/ErrorCollector.php';

class HTMLPurifier_ErrorCollectorTest extends HTMLPurifier_Harness
{
    
    function setup() {
        generate_mock_once('HTMLPurifier_Language');
        generate_mock_once('HTMLPurifier_Generator');
    }
    
    function test() {
        
        $language = new HTMLPurifier_LanguageMock();
        $language->setReturnValue('getErrorName', 'Error', array(E_ERROR));
        $language->setReturnValue('getErrorName', 'Warning', array(E_WARNING));
        $language->setReturnValue('getMessage', 'Message 1', array('message-1'));
        $language->setReturnValue('formatMessage', 'Message 2', array('message-2', array(1 => 'param')));
        $language->setReturnValue('formatMessage', ' at line 23', array('ErrorCollector: At line', array('line' => 23)));
        $language->setReturnValue('formatMessage', ' at line 3', array('ErrorCollector: At line', array('line' => 3)));
        
        $line = false;
        
        $context = new HTMLPurifier_Context();
        $context->register('Locale', $language);
        $context->register('CurrentLine', $line);
        
        $generator = new HTMLPurifier_Generator();
        $context->register('Generator', $generator);
        
        $collector = new HTMLPurifier_ErrorCollector($context);
        
        $line = 23;
        $collector->send(E_ERROR, 'message-1');
        
        $line = 3;
        $collector->send(E_WARNING, 'message-2', 'param');
        
        $result = array(
            0 => array(23, E_ERROR, 'Message 1'),
            1 => array(3, E_WARNING, 'Message 2')
        );
        
        $this->assertIdentical($collector->getRaw(), $result);
        
        $formatted_result = 
            '<ul><li><strong>Warning</strong>: Message 2 at line 3</li>'.
            '<li><strong>Error</strong>: Message 1 at line 23</li></ul>';
        
        $config = HTMLPurifier_Config::create(array('Core.MaintainLineNumbers' => true));
        
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
        
    }
    
    function testNoErrors() {
        $language = new HTMLPurifier_LanguageMock();
        $language->setReturnValue('getMessage', 'No errors', array('ErrorCollector: No errors'));
        $context = new HTMLPurifier_Context();
        $context->register('Locale', $language);
        
        $generator = new HTMLPurifier_Generator();
        $context->register('Generator', $generator);
        
        $collector = new HTMLPurifier_ErrorCollector($context);
        $formatted_result = '<p>No errors</p>';
        $config = HTMLPurifier_Config::createDefault();
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
    }
    
    function testNoLineNumbers() {
        $language = new HTMLPurifier_LanguageMock();
        $language->setReturnValue('getMessage', 'Message 1', array('message-1'));
        $language->setReturnValue('getMessage', 'Message 2', array('message-2'));
        $language->setReturnValue('getErrorName', 'Error', array(E_ERROR));
        $context = new HTMLPurifier_Context();
        $context->register('Locale', $language);
        
        $generator = new HTMLPurifier_Generator();
        $context->register('Generator', $generator);
        
        $collector = new HTMLPurifier_ErrorCollector($context);
        $collector->send(E_ERROR, 'message-1');
        $collector->send(E_ERROR, 'message-2');
        
        $result = array(
            0 => array(null, E_ERROR, 'Message 1'),
            1 => array(null, E_ERROR, 'Message 2')
        );
        $this->assertIdentical($collector->getRaw(), $result);
        
        $formatted_result = 
            '<ul><li><strong>Error</strong>: Message 1</li>'.
            '<li><strong>Error</strong>: Message 2</li></ul>';
        $config = HTMLPurifier_Config::createDefault();
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
    }
    
    function testContextSubstitutions() {
        
        $language = new HTMLPurifier_LanguageMock();
        $context  = new HTMLPurifier_Context();
        $context->register('Locale', $language);
        
        $generator = new HTMLPurifier_Generator();
        $context->register('Generator', $generator);
        
        $current_token = false;
        $context->register('CurrentToken', $current_token);
        
        $collector = new HTMLPurifier_ErrorCollector($context);
        
        // 0
        $current_token = new HTMLPurifier_Token_Start('a', array('href' => 'http://example.com'), 32);
        $language->setReturnValue('formatMessage', 'Token message',
          array('message-data-token', array('CurrentToken' => $current_token)));
        $collector->send(E_NOTICE, 'message-data-token');
        
        $current_attr  = 'href';
        $language->setReturnValue('formatMessage', '$CurrentAttr.Name => $CurrentAttr.Value',
          array('message-attr', array('CurrentToken' => $current_token)));
        
        // 1
        $collector->send(E_NOTICE, 'message-attr'); // test when context isn't available
        
        // 2
        $context->register('CurrentAttr', $current_attr);
        $collector->send(E_NOTICE, 'message-attr');
        
        $result = array(
            0 => array(32, E_NOTICE, 'Token message'),
            1 => array(32, E_NOTICE, '$CurrentAttr.Name => $CurrentAttr.Value'),
            2 => array(32, E_NOTICE, 'href => http://example.com')
        );
        $this->assertIdentical($collector->getRaw(), $result);
        
    }
    
}

