<?php

require_once 'HTMLPurifier/InjectorHarness.php';
require_once 'HTMLPurifier/Injector/AutoParagraph.php';

class HTMLPurifier_Injector_AutoParagraphTest extends HTMLPurifier_InjectorHarness
{
    
    function setup() {
        parent::setup();
        $this->config = array('AutoFormat.AutoParagraph' => true);
    }
    
    function test() {
        $this->assertResult(
            'Foobar',
            '<p>Foobar</p>'
        );
        
        $this->assertResult(
'Par 1
Par 1 still',
'<p>Par 1
Par 1 still</p>'
        );
        
        $this->assertResult(
'Par1

Par2',
            '<p>Par1</p><p>Par2</p>'
        );
        
        $this->assertResult(
'Par1

 

Par2',
            '<p>Par1</p><p>Par2</p>'
        );
        
        $this->assertResult(
'<b>Par1</b>

<i>Par2</i>',
            '<p><b>Par1</b></p><p><i>Par2</i></p>'
        );
        
        
        $this->assertResult(
'<b>Par1

Par2</b>',
'<p><b>Par1

Par2</b></p>'
        );
        
        $this->assertResult(
            'Par1<p>Par2</p>',
            '<p>Par1</p><p>Par2</p>'
        );
        
        $this->assertResult(
            '<b>Par1',
            '<p><b>Par1</b></p>'
        );
        
        $this->assertResult(
'<pre>Par1

Par1</pre>'
        );
        
        $this->assertResult(
'Par1

  ',
'<p>Par1</p>'
        );
        $this->assertResult(
'Par1

<div>Par2</div>

Par3',
'<p>Par1</p><div>Par2</div><p>Par3</p>'
        );
        
        $this->assertResult(
'Par<b>1</b>',
            '<p>Par<b>1</b></p>'
        );
        
        $this->assertResult(
'

Par',
            '<p>Par</p>'
        );
        
        $this->assertResult(
'

Par

',
            '<p>Par</p>'
        );
        
        $this->assertResult(
'<div>Par1

Par2</div>',
            '<div><p>Par1</p><p>Par2</p></div>'
        );
        
        $this->assertResult(
'<div><b>Par1</b>

Par2</div>',
            '<div><p><b>Par1</b></p><p>Par2</p></div>'
        );
        
        $this->assertResult('<div>Par1</div>');
        
        $this->assertResult(
'<div><b>Par1</b>

<i>Par2</i></div>',
            '<div><p><b>Par1</b></p><p><i>Par2</i></p></div>'
        );
        
        $this->assertResult(
'<pre><b>Par1</b>

<i>Par2</i></pre>',
            true
        );
        
        $this->assertResult(
'<div><p>Foo

Bar</p></div>',
            '<div><p>Foo</p><p>Bar</p></div>'
        );
        
        $this->assertResult(
'<div><p><b>Foo</b>

<i>Bar</i></p></div>',
            '<div><p><b>Foo</b></p><p><i>Bar</i></p></div>'
        );
        
        $this->assertResult(
'<div><b>Foo</b></div>',
            '<div><b>Foo</b></div>'
        );
        
        $this->assertResult(
'<blockquote>Par1

Par2</blockquote>',
            '<blockquote><p>Par1</p><p>Par2</p></blockquote>'
        );
        
        $this->assertResult(
'<ul><li>Foo</li>

<li>Bar</li></ul>', true
        );
        
        $this->assertResult(
'<div>

Bar

</div>', 
        '<div><p>Bar</p></div>'
        );
        
        $this->assertResult(
'<b>Par1</b>a



Par2', 
        '<p><b>Par1</b>a</p><p>Par2</p>'
        );
        
        $this->assertResult(
'Par1

Par2</p>', 
        '<p>Par1</p><p>Par2</p>'
        );
        
        $this->assertResult(
'Par1

Par2</div>', 
        '<p>Par1</p><p>Par2</p>'
        );
        
        $this->assertResult(
'<div>
Par1
</div>', true
        );
        
        $this->assertResult(
'<div>Par1

<div>Par2</div></div>',
'<div><p>Par1</p><div>Par2</div></div>'
        );
        
        $this->assertResult(
'<div>Par1
<div>Par2</div></div>',
'<div><p>Par1
</p><div>Par2</div></div>'
        );
        
        $this->assertResult(
'Par1
<div>Par2</div>',
'<p>Par1
</p><div>Par2</div>'
        );
        
        $this->assertResult(
'Par1

<b>Par2</b>',
'<p>Par1</p><p><b>Par2</b></p>'
        );
        
        $this->assertResult(
'<img /> Foo',
'<p><img /> Foo</p>'
        );
        
        $this->assertResult(
'<li>Foo <a>bar</a></li>'
        );
        
        $this->assertResult(
'<li><b>baz</b><a>bar</a></li>'
        );
        
        $this->assertResult(
'<div><div>asdf</div><b>asdf</b></div>'
        );
        
        $this->assertResult(
'<div><div>asdf</div>

<b>asdf</b></div>',
'<div><div>asdf</div><p><b>asdf</b></p></div>'
        );
        
        $this->assertResult(
'<b>One</b> <i>Two</i>',
'<p><b>One</b> <i>Two</i></p>'
        );
        
    }
    
    function testInlineRootNode() {
        $this->assertResult(
'Par

Par2',
            true,
            array('AutoFormat.AutoParagraph' => true, 'HTML.Parent' => 'span')
        );
    }
    
    function testNeeded() {
        $this->expectError('Cannot enable AutoParagraph injector because p is not allowed');
        $this->assertResult('<b>foobar</b>', true, array('AutoFormat.AutoParagraph' => true, 'HTML.Allowed' => 'b'));
    }
    
}

