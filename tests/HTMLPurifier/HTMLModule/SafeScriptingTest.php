<?php

class HTMLPurifier_HTMLModule_SafeScriptingTest extends HTMLPurifier_HTMLModuleHarness
{

    public function setUp()
    {
        parent::setUp();
        $this->config->set('HTML.SafeScripting', array('http://localhost/foo.js'));
    }

    public function testMinimal()
    {
        $this->assertResult(
            '<script></script>',
            ''
        );
    }

    public function testGood()
    {
        $this->assertResult(
            '<script type="text/javascript" src="http://localhost/foo.js"></script>'
        );
    }

    public function testGoodWithAutoclosedTag()
    {
        $this->assertResult(
            '<script type="text/javascript" src="http://localhost/foo.js"/>',
            '<script type="text/javascript" src="http://localhost/foo.js"></script>'
        );
    }

    public function testBad()
    {
        $this->assertResult(
            '<script type="text/javascript" src="http://localhost/foobar.js" />',
            ''
        );
        $this->assertResult(
            '<script type="text/javascript" src="http://localhost/FOO.JS" />',
            ''
        );
    }

}

// vim: et sw=4 sts=4
