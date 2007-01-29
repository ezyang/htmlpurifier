<?php

require_once 'HTMLPurifier/Language.php';

class HTMLPurifier_LanguageTest extends UnitTestCase
{
    
    var $lang;
    
    function setup() {
        $factory = HTMLPurifier_LanguageFactory::instance();
        $this->lang = $factory->create('en');
    }
    
    function test_getMessage() {
        $this->assertIdentical($this->lang->getMessage('htmlpurifier'), 'HTML Purifier');
        $this->assertIdentical($this->lang->getMessage('totally-non-existent-key'), '');
    }
    
}

?>