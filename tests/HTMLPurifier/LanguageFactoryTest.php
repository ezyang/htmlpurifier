<?php

require_once 'HTMLPurifier/LanguageFactory.php';

class HTMLPurifier_LanguageFactoryTest extends HTMLPurifier_Harness
{
    
    function test() {
        
        $factory = HTMLPurifier_LanguageFactory::instance();
        
        $config = HTMLPurifier_Config::create(array('Core.Language' => 'en'));
        $context = new HTMLPurifier_Context();
        $language = $factory->create($config, $context);
        
        $this->assertIsA($language, 'HTMLPurifier_Language');
        $this->assertIdentical($language->code, 'en');
        
        // lazy loading test
        $this->assertIdentical(count($language->messages), 0);
        $language->load();
        $this->assertNotEqual(count($language->messages), 0);
        
        // actual tests for content can be found in LanguageTest
        
    }
    
    function testFallback() {
        
        $factory = HTMLPurifier_LanguageFactory::instance();
        
        $config = HTMLPurifier_Config::create(array('Core.Language' => 'en-x-test'));
        $context = new HTMLPurifier_Context();
        
        $language = $factory->create($config, $context);
        
        $this->assertIsA($language, 'HTMLPurifier_Language_en_x_test');
        $this->assertIdentical($language->code, 'en-x-test');
        
        $language->load();
        
        // test overloaded message
        $this->assertIdentical($language->getMessage('HTMLPurifier'), 'HTML Purifier X');
        
        // test inherited message
        $this->assertIdentical($language->getMessage('LanguageFactoryTest: Pizza'), 'Pizza');
        
    }
    
}

