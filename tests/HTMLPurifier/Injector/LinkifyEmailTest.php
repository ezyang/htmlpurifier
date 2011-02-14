<?php

class HTMLPurifier_Injector_LinkifyEmailTest extends HTMLPurifier_InjectorHarness
{

    function setup() {
        parent::setup();
        $this->config->set('AutoFormat.LinkifyEmail', true);
    }

    function testLinkifyEmailInRootNode() {
        $this->assertResult(
            'user@example.com',
            '<a href="mailto:user@example.com">user@example.com</a>'
        );
    }

    function testLinkifyEmailLInInlineNode() {
        $this->assertResult(
            '<b>user@example.com</b>',
            '<b><a href="mailto:user@example.com">user@example.com</a></b>'
        );
    }

    function testBasicUsageCase() {
        $this->assertResult(
            'This e-mail user@example.com is what you need',
            'This e-mail <a href="mailto:user@example.com">user@example.com</a> is what you need'
        );
    }

    function testIgnoreEmailInATag() {
        $this->assertResult(
            '<a>user@example.com</a>'
        );
    }

    function testNeeded() {
        $this->config->set('HTML.Allowed', 'b');
        $this->expectError('Cannot enable LinkifyEmail injector because a is not allowed');
        $this->assertResult('user@example.com');
    }

    function testExcludes() {
        $this->assertResult('<a><span>user@example.com</span></a>');
    }

}

// vim: et sw=4 sts=4
