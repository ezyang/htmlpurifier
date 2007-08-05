<?php

require_once 'HTMLPurifier/InjectorHarness.php';
require_once 'HTMLPurifier/Injector/Linkify.php';

class HTMLPurifier_Injector_LinkifyTest extends HTMLPurifier_InjectorHarness
{
    
    function setup() {
        parent::setup();
        $this->config = array('AutoFormat.Linkify' => true);
    }
    
    function testLinkify() {
        
        $this->assertResult(
            'http://example.com',
            '<a href="http://example.com">http://example.com</a>'
        );
        
        $this->assertResult(
            '<b>http://example.com</b>',
            '<b><a href="http://example.com">http://example.com</a></b>'
        );
        
        $this->assertResult(
            'This URL http://example.com is what you need',
            'This URL <a href="http://example.com">http://example.com</a> is what you need'
        );
        
        $this->assertResult(
            '<a>http://example.com/</a>'
        );
        
    }
    
    function testNeeded() {
        $this->expectError('Cannot enable Linkify injector because a is not allowed');
        $this->assertResult('http://example.com/', true, array('AutoFormat.Linkify' => true, 'HTML.Allowed' => 'b'));
    }
    
}

