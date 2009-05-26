<?php

class HTMLPurifier_AttrDef_HTML_ClassTest extends HTMLPurifier_AttrDef_HTML_NmtokensTest
{
    function setUp() {
        parent::setUp();
        $this->def = new HTMLPurifier_AttrDef_HTML_Class();
    }
    function testAllowedClasses() {
        $this->config->set('Attr.AllowedClasses', array('foo'));
        $this->assertDef('foo');
        $this->assertDef('bar', false);
        $this->assertDef('foo bar', 'foo');
    }
    function testForbiddenClasses() {
        $this->config->set('Attr.ForbiddenClasses', array('bar'));
        $this->assertDef('foo');
        $this->assertDef('bar', false);
        $this->assertDef('foo bar', 'foo');
    }
}
