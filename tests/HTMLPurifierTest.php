<?php

require_once 'HTMLPurifier.php';

// integration test

class HTMLPurifierTest extends HTMLPurifier_Harness
{
    var $purifier;
    
    function setUp() {
        $this->purifier = new HTMLPurifier();
    }
    
    function assertPurification($input, $expect = null, $config = array()) {
        if ($expect === null) $expect = $input;
        $result = $this->purifier->purify($input, $config);
        $this->assertIdentical($expect, $result);
    }
    
    function testNull() {
        $this->assertPurification("Null byte\0", "Null byte");
    }
    
    function testStrict() {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML', 'Strict', true);
        $this->purifier = new HTMLPurifier( $config ); // verbose syntax
        
        $this->assertPurification(
            '<u>Illegal underline</u>',
            '<span style="text-decoration:underline;">Illegal underline</span>'
        );
        
        $this->assertPurification(
            '<blockquote>Illegal contents</blockquote>',
            '<blockquote><p>Illegal contents</p></blockquote>'
        );
        
    }
    
    function testDifferentAllowedElements() {
        
        $this->purifier = new HTMLPurifier(array(
            'HTML.AllowedElements' => array('b', 'i', 'p', 'a'),
            'HTML.AllowedAttributes' => array('a.href', '*.id')
        ));
        
        $this->assertPurification(
            '<p>Par.</p><p>Para<a href="http://google.com/">gr</a>aph</p>Text<b>Bol<i>d</i></b>'
        );
        
        $this->assertPurification(
            '<span>Not allowed</span><a class="mef" id="foobar">Foobar</a>',
            'Not allowed<a>Foobar</a>' // no ID!!!
        );
        
    }
    
    function testDisableURI() {
        
        $this->purifier = new HTMLPurifier( array('Attr.DisableURI' => true) );
        
        $this->assertPurification(
            '<img src="foobar"/>',
            ''
        );
        
    }
    
    function test_purifyArray() {
        
        $this->purifier = new HTMLPurifier();
        
        $this->assertIdentical(
            $this->purifier->purifyArray(
                array('Good', '<b>Sketchy', 'foo' => '<script>bad</script>')
            ),
            array('Good', '<b>Sketchy</b>', 'foo' => '')
        );
        
        $this->assertIsA($this->purifier->context, 'array');
        
    }
    
    function testEnableAttrID() {
        
        $this->purifier = new HTMLPurifier();
        
        $this->assertPurification(
            '<span id="moon">foobar</span>',
            '<span>foobar</span>'
        );
        
        $this->purifier = new HTMLPurifier(array('HTML.EnableAttrID' => true));
        $this->assertPurification('<span id="moon">foobar</span>');
        
    }
    
    function testScript() {
        $this->purifier = new HTMLPurifier(array('HTML.Trusted' => true));
        $ideal = '<script type="text/javascript"><!--//--><![CDATA[//><!--
alert("<This is compatible with XHTML>");
//--><!]]></script>';
        
        $this->assertPurification($ideal);
        
        $this->assertPurification(
            '<script type="text/javascript"><![CDATA[
alert("<This is compatible with XHTML>");
]]></script>',
            $ideal
        );
        
        $this->assertPurification(
            '<script type="text/javascript">alert("<This is compatible with XHTML>");</script>',
            $ideal
        );
        
        $this->assertPurification(
            '<script type="text/javascript"><!--
alert("<This is compatible with XHTML>");
//--></script>',
            $ideal
        );
        
        $this->assertPurification(
            '<script type="text/javascript"><![CDATA[
alert("<This is compatible with XHTML>");
//]]></script>',
            $ideal
        );
    }
    
    function testGetInstance() {
        $purifier  =& HTMLPurifier::getInstance();
        $purifier2 =& HTMLPurifier::getInstance();
        $this->assertReference($purifier, $purifier2);
    }
    
    function testMakeAbsolute() {
        $this->assertPurification(
            '<a href="foo.txt">Foobar</a>',
            '<a href="http://example.com/bar/foo.txt">Foobar</a>',
            array(
                'URI.Base' => 'http://example.com/bar/baz.php',
                'URI.MakeAbsolute' => true
            )
        );
    }
    
}

