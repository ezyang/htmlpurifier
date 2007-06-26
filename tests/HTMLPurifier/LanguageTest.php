<?php

require_once 'HTMLPurifier/Language.php';

class HTMLPurifier_LanguageTest extends UnitTestCase
{
    
    var $lang;
    
    function test_getMessage() {
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        $lang = new HTMLPurifier_Language($config, $context);
        $lang->_loaded = true;
        $lang->messages['HTMLPurifier'] = 'HTML Purifier';
        $this->assertIdentical($lang->getMessage('HTMLPurifier'), 'HTML Purifier');
        $this->assertIdentical($lang->getMessage('LanguageTest: Totally non-existent key'), '[LanguageTest: Totally non-existent key]');
    }
    
    function test_formatMessage() {
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        $lang = new HTMLPurifier_Language($config, $context);
        $lang->_loaded = true;
        $lang->messages['LanguageTest: Error'] = 'Error is $1 on line $2';
        $this->assertIdentical($lang->formatMessage('LanguageTest: Error', array(1=>'fatal', 32)), 'Error is fatal on line 32');
    }
    
    function test_formatMessage_complexParameter() {
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        $generator = new HTMLPurifier_Generator(); // replace with mock if this gets icky
        $context->register('Generator', $generator);
        $lang = new HTMLPurifier_Language($config, $context);
        $lang->_loaded = true;
        $lang->messages['LanguageTest: Element info'] = 'Element Token: $1.Name, $1.Serialized, $1.Compact, $1.Line';
        $lang->messages['LanguageTest: Data info']    = 'Data Token: $1.Data, $1.Serialized, $1.Compact, $1.Line';
        $this->assertIdentical($lang->formatMessage('LanguageTest: Element info',
            array(1=>new HTMLPurifier_Token_Start('a', array('href'=>'http://example.com'), 18))),
            'Element Token: a, <a href="http://example.com">, <a>, 18');
        $this->assertIdentical($lang->formatMessage('LanguageTest: Data info',
            array(1=>new HTMLPurifier_Token_Text('data>', 23))),
            'Data Token: data>, data&gt;, data&gt;, 23');
    }
    
}

?>