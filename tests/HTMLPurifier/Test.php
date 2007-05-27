<?php

require_once 'HTMLPurifier.php';

// integration test

class HTMLPurifier_Test extends UnitTestCase
{
    var $purifier;
    
    function setUp() {
        $this->purifier = new HTMLPurifier();
    }
    
    function assertPurification($input, $expect = null) {
        if ($expect === null) $expect = $input;
        $result = $this->purifier->purify($input);
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
            array('Good', '<b>Sketchy</b>', 'foo' => 'bad')
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
    
    function test_table() {
        
        $this->purifier = new HTMLPurifier();
        $this->assertPurification(
'<TABLE><COLGROUP><COL span=3 width=64 /><TBODY><TR><TD>1</TD><TD>2</TD><TD>3</TD></TR></TBODY></TABLE>',
'<table><colgroup><col span="3" width="64" /></colgroup><tbody><tr><td>1</td><td>2</td><td>3</td></tr></tbody></table>'
        );
        
    }
    
}

?>