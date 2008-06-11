<?php

require_once 'HTMLPurifier.php';

class HTMLPurifierTest extends HTMLPurifier_Harness
{
    
    function testNull() {
        $this->assertPurification("Null byte\0", "Null byte");
    }
    
    function testStrict() {
        $this->config->set('HTML', 'Strict', true);
        
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
        
        $this->config->set('HTML', 'AllowedElements', array('b', 'i', 'p', 'a'));
        $this->config->set('HTML', 'AllowedAttributes', array('a.href', '*.id'));
        
        $this->assertPurification(
            '<p>Par.</p><p>Para<a href="http://google.com/">gr</a>aph</p>Text<b>Bol<i>d</i></b>'
        );
        
        $this->assertPurification(
            '<span>Not allowed</span><a class="mef" id="foobar">Foobar</a>',
            'Not allowed<a>Foobar</a>' // no ID!!!
        );
        
    }
    
    function testDisableURI() {
        
        $this->config->set('URI', 'Disable', true);
        
        $this->assertPurification(
            '<img src="foobar"/>',
            ''
        );
        
    }
    
    function test_purifyArray() {
        
        $this->assertIdentical(
            $this->purifier->purifyArray(
                array('Good', '<b>Sketchy', 'foo' => '<script>bad</script>')
            ),
            array('Good', '<b>Sketchy</b>', 'foo' => '')
        );
        
        $this->assertIsA($this->purifier->context, 'array');
        
    }
    
    function testAttrIDDisabledByDefault() {
        
        $this->assertPurification(
            '<span id="moon">foobar</span>',
            '<span>foobar</span>'
        );
        
    }
    
    function testEnableAttrID() {
        $this->config->set('Attr', 'EnableID', true);
        $this->assertPurification('<span id="moon">foobar</span>');
        $this->assertPurification('<img id="folly" src="folly.png" alt="Omigosh!" />');
    }
    
    function testScript() {
        $this->config->set('HTML', 'Trusted', true);
        
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
        $this->config->set('URI', 'Base', 'http://example.com/bar/baz.php');
        $this->config->set('URI', 'MakeAbsolute', true);
        $this->assertPurification(
            '<a href="foo.txt">Foobar</a>',
            '<a href="http://example.com/bar/foo.txt">Foobar</a>'
        );
    }
    
    function test_shiftJis() {
        if (!function_exists('iconv')) return;
        $this->config->set('Core', 'Encoding', 'Shift_JIS');
        $this->config->set('Core', 'EscapeNonASCIICharacters', true);
        $this->assertPurification(
            "<b style=\"font-family:'&#165;';\">111</b>"
        );
    }
    
    function test_shiftJisWorstCase() {
        if (!function_exists('iconv')) return;
        $this->config->set('Core', 'Encoding', 'Shift_JIS');
        $this->assertPurification( // Notice how Yen disappears
            "<b style=\"font-family:'&#165;';\">111</b>",
            "<b style=\"font-family:'';\">111</b>"
        );
    }
    
}

