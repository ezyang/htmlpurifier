<?php

class HTMLPurifier_HTMLModule_SafeScriptingTest extends HTMLPurifier_HTMLModuleHarness
{
    public function setUp()
    {
        parent::setUp();

        $this->config->set('HTML.SafeScripting', array(
            'http://localhost/foo.js',
            'http://localhost/fooCaseSensitive.js',
            'http://localhost/foocaseinsensitive.js',
        ));
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
            '<script type="text/javascript" src="http://localhost/foo.js" />'
        );
    }

    public function testBad()
    {
        $this->assertResult(
            '<script type="text/javascript" src="http://localhost/foobar.js" />',
            ''
        );
    }

    public function testGoodWithCaseInsensitive()
    {
        $this->assertResult(
            '<script type="text/javascript" src="http://localhost/fooCaseInsensitive.js" />',
            '<script type="text/javascript" src="http://localhost/foocaseinsensitive.js" />'
        );
    }

    public function testGoodWithCaseSensitive()
    {
        $this->config->set('HTML.SafeScripting.CaseSensitive', true);

        $this->assertResult(
            '<script type="text/javascript" src="http://localhost/fooCaseSensitive.js" />'
        );
    }
}

// vim: et sw=4 sts=4
