<?php

require_once 'HTMLPurifier/LanguageFactory.php';

class HTMLPurifier_LanguageFactoryTest extends UnitTestCase
{
    
    function test() {
        
        $factory = HTMLPurifier_LanguageFactory::instance();
        
        $language = $factory->create('en');
        
        $this->assertIsA($language, 'HTMLPurifier_Language');
        $this->assertEqual($language->code, 'en');
        
        // lazy loading test
        $this->assertEqual(count($language->messages), 0);
        $language->load();
        $this->assertNotEqual(count($language->messages), 0);
        
        // actual tests for content can be found in LanguageTest
        
    }
    
    function testFallback() {
        
        $factory = HTMLPurifier_LanguageFactory::instance();
        
        $language = $factory->create('en-x-test');
        
        $this->assertIsA($language, 'HTMLPurifier_Language_en_x_test');
        $this->assertEqual($language->code, 'en-x-test');
        
        $language->load();
        
        // test overloaded message
        $this->assertEqual($language->getMessage('htmlpurifier'), 'HTML Purifier X');
        
        // test inherited message
        $this->assertEqual($language->getMessage('pizza'), 'Pizza');
        
    }
    
}

?>