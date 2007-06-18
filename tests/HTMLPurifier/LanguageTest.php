<?php

require_once 'HTMLPurifier/Language.php';

class HTMLPurifier_LanguageTest extends UnitTestCase
{
    
    var $lang;
    
    function test_getMessage() {
        $lang = new HTMLPurifier_Language();
        $lang->_loaded = true;
        $lang->messages['htmlpurifier'] = 'HTML Purifier';
        $this->assertIdentical($lang->getMessage('htmlpurifier'), 'HTML Purifier');
        $this->assertIdentical($lang->getMessage('totally-non-existent-key'), '[totally-non-existent-key]');
    }
    
    function test_formatMessage() {
        $lang = new HTMLPurifier_Language();
        $lang->_loaded = true;
        $lang->messages['error'] = 'Error is $1 on line $2';
        $this->assertIdentical($lang->formatMessage('error', 'fatal', 32), 'Error is fatal on line 32');
    }
    
}

?>