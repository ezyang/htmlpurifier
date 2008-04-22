<?php

class HTMLPurifierTest extends HTMLPurifier_Harness
{
    protected $purifier;
    
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
    
    function testBlacklistElements() {
        $this->purifier = new HTMLPurifier(array(
            'HTML.ForbiddenElements' => array('b'),
            'HTML.ForbiddenAttributes' => array('a.href')
        ));
        $this->assertPurification(
            '<p>Par.</p>'
        );
        $this->assertPurification(
            '<b>Pa<a href="foo">r</a>.</b>',
            'Pa<a>r</a>.'
        );
        
    }
    
    function testDifferentAllowedCSSProperties() {
        
        $this->purifier = new HTMLPurifier(array(
            'CSS.AllowedProperties' => array('color', 'background-color')
        ));
        
        $this->assertPurification(
            '<div style="color:#f00;background-color:#ded;">red</div>'
        );
        
        $this->assertPurification(
            '<div style="color:#f00;border:1px solid #000">red</div>',
            '<div style="color:#f00;">red</div>'
        );
        
    }
    
    function testDisableURI() {
        
        $this->purifier = new HTMLPurifier( array('URI.Disable' => true) );
        
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
        
        $this->purifier = new HTMLPurifier(array('Attr.EnableID' => true));
        $this->assertPurification('<span id="moon">foobar</span>');
        $this->assertPurification('<img id="folly" src="folly.png" alt="Omigosh!" />');
        
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
        $purifier  = HTMLPurifier::getInstance();
        $purifier2 = HTMLPurifier::getInstance();
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
    
    function test_addFilter_deprecated() {
        $purifier = new HTMLPurifier();
        $this->expectError('HTMLPurifier->addFilter() is deprecated, use configuration directives in the Filter namespace or Filter.Custom');
        generate_mock_once('HTMLPurifier_Filter');
        $purifier->addFilter($mock = new HTMLPurifier_FilterMock());
        $mock->expectOnce('preFilter');
        $mock->expectOnce('postFilter');
        $purifier->purify('foo');
    }
    
}

