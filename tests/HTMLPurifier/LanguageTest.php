<?php

require_once 'HTMLPurifier/Language.php';

class HTMLPurifier_LanguageTest extends UnitTestCase
{
    
    var $lang;
    
    function test_getMessage() {
        $lang = new HTMLPurifier_Language();
        $lang->_loaded = true;
        $lang->messages['HTMLPurifier'] = 'HTML Purifier';
        $this->assertIdentical($lang->getMessage('HTMLPurifier'), 'HTML Purifier');
        $this->assertIdentical($lang->getMessage('LanguageTest: Totally non-existent key'), '[LanguageTest: Totally non-existent key]');
    }
    
    function test_formatMessage() {
        $lang = new HTMLPurifier_Language();
        $lang->_loaded = true;
        $lang->messages['LanguageTest: Error'] = 'Error is $1 on line $2';
        $this->assertIdentical($lang->formatMessage('LanguageTest: Error', array(1=>'fatal', 32)), 'Error is fatal on line 32');
    }
    
}

?>