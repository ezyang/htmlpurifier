<?php

class HTMLPurifier_Strategy_RemoveForeignElements_TidyTest
  extends HTMLPurifier_StrategyHarness
{

    public function setUp()
    {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_RemoveForeignElements();
        $this->config->set('HTML.TidyLevel', 'heavy');
    }

    public function testCenterTransform()
    {
        $this->assertResult(
            '<center>Look I am Centered!</center>',
            '<div style="text-align:center;">Look I am Centered!</div>'
        );
    }

    public function testFontTransform()
    {
        $this->assertResult(
            '<font color="red" face="Arial" size="6">Big Warning!</font>',
            '<span style="color:red;font-family:Arial;font-size:xx-large;">Big'.
              ' Warning!</span>'
        );
    }

    public function testTransformToForbiddenElement()
    {
        $this->config->set('HTML.Allowed', 'div');
        $this->assertResult(
            '<font color="red" face="Arial" size="6">Big Warning!</font>',
            'Big Warning!'
        );
    }

    public function testMenuTransform()
    {
        $this->assertResult(
            '<menu><li>Item 1</li></menu>',
            '<ul><li>Item 1</li></ul>'
        );
    }

}

// vim: et sw=4 sts=4
