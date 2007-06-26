<?php

require_once 'HTMLPurifier/ErrorCollector.php';

class HTMLPurifier_ErrorCollectorTest extends UnitTestCase
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
        
        $generator = new HTMLPurifier_GeneratorMock();
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
            '<ul><li>Warning: Message 2 at line 3</li>'.
            '<li>Error: Message 1 at line 23</li></ul>';
        
        $config = HTMLPurifier_Config::create(array('Core.MaintainLineNumbers' => true));
        
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
        
    }
    
    function testNoErrors() {
        $language = new HTMLPurifier_LanguageMock();
        $language->setReturnValue('getMessage', 'No errors', array('ErrorCollector: No errors'));
        $context = new HTMLPurifier_Context();
        $context->register('Locale', $language);
        
        $generator = new HTMLPurifier_GeneratorMock();
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
        
        $generator = new HTMLPurifier_GeneratorMock();
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
            '<ul><li>Error: Message 1</li>'.
            '<li>Error: Message 2</li></ul>';
        $config = HTMLPurifier_Config::createDefault();
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
    }
    
    function testContextSubstitutions() {
        
        $language = new HTMLPurifier_LanguageMock();
        $language->setReturnValue('getMessage',
            '$CurrentToken.Name, $CurrentToken.Serialized', array('message-token'));
        $language->setReturnValue('getMessage',
            '$CurrentAttr.Name => $CurrentAttr.Value', array('message-attr'));
        $context = new HTMLPurifier_Context();
        $context->register('Locale', $language);
        
        $current_token = new HTMLPurifier_Token_Start('a', array('href' => 'http://example.com'));
        $current_token->line = 32;
        $current_attr  = 'href';
        
        $generator = new HTMLPurifier_GeneratorMock();
        $generator->setReturnValue('generateFromToken', '<a href="http://example.com">', array($current_token));
        $context->register('Generator', $generator);
        
        $collector = new HTMLPurifier_ErrorCollector($context);
        
        $context->register('CurrentToken', $current_token);
        $collector->send(E_NOTICE, 'message-token');
        $collector->send(E_NOTICE, 'message-attr'); // test when context isn't available
        
        $context->register('CurrentAttr', $current_attr);
        $collector->send(E_NOTICE, 'message-attr');
        
        $result = array(
            0 => array(32, E_NOTICE, 'a, <a href="http://example.com">'),
            1 => array(32, E_NOTICE, '$CurrentAttr.Name => $CurrentAttr.Value'),
            2 => array(32, E_NOTICE, 'href => http://example.com')
        );
        $this->assertIdentical($collector->getRaw(), $result);
        
    }
    
}

?>