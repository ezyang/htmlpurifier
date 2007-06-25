<?php

require_once 'HTMLPurifier/ErrorCollector.php';

class HTMLPurifier_ErrorCollectorTest extends UnitTestCase
{
    
    function setup() {
        generate_mock_once('HTMLPurifier_Language');
    }
    
    function test() {
        
        $tok = new HTMLPurifier_Token_Start('a'); // also caused error
        $tok->line = 3;
        
        $language = new HTMLPurifier_LanguageMock();
        $language->setReturnValue('getErrorName', 'Error', array(E_ERROR));
        $language->setReturnValue('getErrorName', 'Warning', array(E_WARNING));
        $language->setReturnValue('getMessage', 'Message 1', array('message-1'));
        $language->setReturnValue('formatMessage', 'Message 2', array('message-2', array(1 => 'param')));
        $language->setReturnValue('formatMessage', ' at line 23', array('ErrorCollector: At line', array('line' => 23)));
        $language->setReturnValue('formatMessage', ' at line 3', array('ErrorCollector: At line', array('line' => 3)));
        
        $collector = new HTMLPurifier_ErrorCollector($language);
        $collector->send(23, E_ERROR, 'message-1');
        $collector->send($tok, E_WARNING, 'message-2', 'param');
        
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
        $collector = new HTMLPurifier_ErrorCollector($language);
        $formatted_result = '<p>No errors</p>';
        $config = HTMLPurifier_Config::createDefault();
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
    }
    
    function testNoLineNumbers() {
        $token = new HTMLPurifier_Token_Start('a'); // no line number!
        $language = new HTMLPurifier_LanguageMock();
        $language->setReturnValue('getMessage', 'Message 1', array('message-1'));
        $language->setReturnValue('getMessage', 'Message 2', array('message-2'));
        $language->setReturnValue('getErrorName', 'Error', array(E_ERROR));
        $collector = new HTMLPurifier_ErrorCollector($language);
        $collector->send(false, E_ERROR, 'message-1');
        $collector->send($token, E_ERROR, 'message-2');
        
        $result = array(
            0 => array(false, E_ERROR, 'Message 1'),
            1 => array(false, E_ERROR, 'Message 2')
        );
        $this->assertIdentical($collector->getRaw(), $result);
        
        $formatted_result = 
            '<ul><li>Error: Message 1</li>'.
            '<li>Error: Message 2</li></ul>';
        $config = HTMLPurifier_Config::createDefault();
        $this->assertIdentical($collector->getHTMLFormatted($config), $formatted_result);
    }
    
}

?>