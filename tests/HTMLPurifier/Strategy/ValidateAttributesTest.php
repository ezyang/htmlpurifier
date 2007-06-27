<?php

require_once('HTMLPurifier/Config.php');
require_once('HTMLPurifier/StrategyHarness.php');
require_once('HTMLPurifier/Strategy/ValidateAttributes.php');

class HTMLPurifier_Strategy_ValidateAttributesTest extends
      HTMLPurifier_StrategyHarness
{
    
    function setUp() {
        parent::setUp();
        $this->obj = new HTMLPurifier_Strategy_ValidateAttributes();
        $this->config = array('HTML.Doctype' => 'XHTML 1.0 Strict');
    }
    
    function testEmpty() {
        $this->assertResult('');
    }
    
    function testIDs() {
        $this->assertResult(
            '<div id="valid">Kill the ID.</div>',
            '<div>Kill the ID.</div>'
        );
            
        $this->assertResult('<div id="valid">Preserve the ID.</div>', true,
            array('HTML.EnableAttrID' => true));
        
        $this->assertResult(
            '<div id="0invalid">Kill the ID.</div>',
            '<div>Kill the ID.</div>',
            array('HTML.EnableAttrID' => true)
        );
        
        // test id accumulator
        $this->assertResult(
            '<div id="valid">Valid</div><div id="valid">Invalid</div>',
            '<div id="valid">Valid</div><div>Invalid</div>',
            array('HTML.EnableAttrID' => true)
        );
        
        $this->assertResult(
            '<span dir="up-to-down">Bad dir.</span>',
            '<span>Bad dir.</span>'
        );
        
        // test attribute key case sensitivity
        $this->assertResult(
            '<div ID="valid">Convert ID to lowercase.</div>',
            '<div id="valid">Convert ID to lowercase.</div>',
            array('HTML.EnableAttrID' => true)
        );
        
        // test simple attribute substitution
        $this->assertResult(
            '<div id=" valid ">Trim whitespace.</div>',
            '<div id="valid">Trim whitespace.</div>',
            array('HTML.EnableAttrID' => true)
        );
        
        // test configuration id blacklist
        $this->assertResult(
            '<div id="invalid">Invalid</div>',
            '<div>Invalid</div>',
            array(
                'Attr.IDBlacklist' => array('invalid'),
                'HTML.EnableAttrID' => true
            )
        );
        
        // name rewritten as id
        $this->assertResult(
            '<a name="foobar" />',
            '<a id="foobar" />',
            array('HTML.EnableAttrID' => true)
        );
    }
    
    function testClasses() {
        $this->assertResult('<div class="valid">Valid</div>');
        
        $this->assertResult(
            '<div class="valid 0invalid">Keep valid.</div>',
            '<div class="valid">Keep valid.</div>'
        );
    }
    
    function testTitle() {
        $this->assertResult(
            '<acronym title="PHP: Hypertext Preprocessor">PHP</acronym>'
        );
    }
    
    function testLang() {
        $this->assertResult(
            '<span lang="fr">La soupe.</span>',
            '<span lang="fr" xml:lang="fr">La soupe.</span>'
        );
        
        // test only xml:lang for XHTML 1.1
        $this->assertResult(
            '<b lang="en">asdf</b>',
            '<b xml:lang="en">asdf</b>', array('HTML.Doctype' => 'XHTML 1.1')
        );
    }
    
    function testAlign() {
        
        $this->assertResult(
            '<h1 align="center">Centered Headline</h1>',
            '<h1 style="text-align:center;">Centered Headline</h1>'
        );
        $this->assertResult(
            '<h1 align="right">Right-aligned Headline</h1>',
            '<h1 style="text-align:right;">Right-aligned Headline</h1>'
        );
        $this->assertResult(
            '<h1 align="left">Left-aligned Headline</h1>',
            '<h1 style="text-align:left;">Left-aligned Headline</h1>'
        );
        $this->assertResult(
            '<p align="justify">Justified Paragraph</p>',
            '<p style="text-align:justify;">Justified Paragraph</p>'
        );
        $this->assertResult(
            '<h1 align="invalid">Invalid Headline</h1>',
            '<h1>Invalid Headline</h1>'
        );
        
    }
    
    function testTable() {
        $this->assertResult(
'<table frame="above" rules="rows" summary="A test table" border="2" cellpadding="5%" cellspacing="3" width="100%">
    <col align="right" width="4*" />
    <col charoff="5" align="char" width="*" />
    <tr valign="top">
        <th abbr="name">Fiddly name</th>
        <th abbr="price">Super-duper-price</th>
    </tr>
    <tr>
        <td abbr="carrot">Carrot Humungous</td>
        <td>$500.23</td>
    </tr>
    <tr>
        <td colspan="2">Taken off the market</td>
    </tr>
</table>'
        );
        
        // test col.span is non-zero
        $this->assertResult(
            '<col span="0" />',
            '<col />'
        );
        // lengths
        $this->assertResult(
            '<td width="5%" height="10" /><th width="10" height="5%" /><hr width="10" height="10" />',
            '<td style="width:5%;height:10px;" /><th style="width:10px;height:5%;" /><hr style="width:10px;" />'
        );
        // td boolean transformation
        $this->assertResult(
            '<td nowrap />',
            '<td style="white-space:nowrap;" />'
        );
        
        // caption align transformation
        $this->assertResult(
            '<caption align="left" />',
            '<caption style="text-align:left;" />'
        );
        $this->assertResult(
            '<caption align="right" />',
            '<caption style="text-align:right;" />'
        );
        $this->assertResult(
            '<caption align="top" />',
            '<caption style="caption-side:top;" />'
        );
        $this->assertResult(
            '<caption align="bottom" />',
            '<caption style="caption-side:bottom;" />'
        );
        $this->assertResult(
            '<caption align="nonsense" />',
            '<caption />'
        );
        
        // align transformation
        $this->assertResult(
            '<table align="left" />',
            '<table style="float:left;" />'
        );
        $this->assertResult(
            '<table align="center" />',
            '<table style="margin-left:auto;margin-right:auto;" />'
        );
        $this->assertResult(
            '<table align="right" />',
            '<table style="float:right;" />'
        );
        $this->assertResult(
            '<table align="top" />',
            '<table />'
        );
    }
    
    function testURI() {
        $this->assertResult('<a href="http://www.google.com/">Google</a>');
        
        // test invalid URI
        $this->assertResult(
            '<a href="javascript:badstuff();">Google</a>',
            '<a>Google</a>'
        );
    }
    
    function testImg() {
        $this->assertResult(
            '<img />',
            '<img src="" alt="Invalid image" />',
            array('Core.RemoveInvalidImg' => false)
        );
        
        $this->assertResult(
            '<img src="foobar.jpg" />',
            '<img src="foobar.jpg" alt="foobar.jpg" />'
        );
        
        $this->assertResult(
            '<img alt="pretty picture" />',
            '<img alt="pretty picture" src="" />',
            array('Core.RemoveInvalidImg' => false)
        );
        // mailto in image is not allowed
        $this->assertResult(
            '<img src="mailto:foo@example.com" />',
            '<img alt="mailto:foo@example.com" src="" />',
            array('Core.RemoveInvalidImg' => false)
        );
        // align transformation
        $this->assertResult(
            '<img src="foobar.jpg" alt="foobar" align="left" />',
            '<img src="foobar.jpg" alt="foobar" style="float:left;" />'
        );
        $this->assertResult(
            '<img src="foobar.jpg" alt="foobar" align="right" />',
            '<img src="foobar.jpg" alt="foobar" style="float:right;" />'
        );
        $this->assertResult(
            '<img src="foobar.jpg" alt="foobar" align="bottom" />',
            '<img src="foobar.jpg" alt="foobar" style="vertical-align:baseline;" />'
        );
        $this->assertResult(
            '<img src="foobar.jpg" alt="foobar" align="middle" />',
            '<img src="foobar.jpg" alt="foobar" style="vertical-align:middle;" />'
        );
        $this->assertResult(
            '<img src="foobar.jpg" alt="foobar" align="top" />',
            '<img src="foobar.jpg" alt="foobar" style="vertical-align:top;" />'
        );
        $this->assertResult(
            '<img src="foobar.jpg" alt="foobar" align="outerspace" />',
            '<img src="foobar.jpg" alt="foobar" />'
        );
        
    }
    
    function testBdo() {
        // test required attributes for bdo
        $this->assertResult(
            '<bdo>Go left.</bdo>',
            '<bdo dir="ltr">Go left.</bdo>'
        );
        
        $this->assertResult(
            '<bdo dir="blahblah">Invalid value!</bdo>',
            '<bdo dir="ltr">Invalid value!</bdo>'
        );
    }
    
    function testDir() {
        // see testBdo, behavior is subtly different
        $this->assertResult(
            '<span dir="blahblah">Invalid value!</span>',
            '<span>Invalid value!</span>'
        );
    }
        
    function testLinks() {
        // link types
        $this->assertResult(
            '<a href="foo" rel="nofollow" />',
            true,
            array('Attr.AllowedRel' => 'nofollow')
        );
        // link targets
        $this->assertResult(
            '<a href="foo" target="_top" />',
            true,
            array('Attr.AllowedFrameTargets' => '_top',
                'HTML.Doctype' => 'XHTML 1.0 Transitional')
        );
        $this->assertResult(
            '<a href="foo" target="_top" />',
            '<a href="foo" />'
        );
        $this->assertResult(
            '<a href="foo" target="_top" />',
            '<a href="foo" />',
            array('Attr.AllowedFrameTargets' => '_top', 'HTML.Strict' => true)
        );
    }
    
    function testBorder() {
        // border
        $this->assertResult(
            '<img src="foo" alt="foo" hspace="1" vspace="3" />',
            '<img src="foo" alt="foo" style="margin-top:3px;margin-bottom:3px;margin-left:1px;margin-right:1px;" />',
            array('Attr.AllowedRel' => 'nofollow')
        );
    }
    
    function testHr() {
        $this->assertResult(
            '<hr size="3" />',
            '<hr style="height:3px;" />'
        );
        $this->assertResult(
            '<hr noshade />',
            '<hr style="color:#808080;background-color:#808080;border:0;" />'
        );
        // align transformation
        $this->assertResult(
            '<hr align="left" />',
            '<hr style="margin-left:0;margin-right:auto;text-align:left;" />'
        );
        $this->assertResult(
            '<hr align="center" />',
            '<hr style="margin-left:auto;margin-right:auto;text-align:center;" />'
        );
        $this->assertResult(
            '<hr align="right" />',
            '<hr style="margin-left:auto;margin-right:0;text-align:right;" />'
        );
        $this->assertResult(
            '<hr align="bottom" />',
            '<hr />'
        );
    }
    
    function testBr() {
        // br clear transformation
        $this->assertResult(
            '<br clear="left" />',
            '<br style="clear:left;" />'
        );
        $this->assertResult(
            '<br clear="right" />',
            '<br style="clear:right;" />'
        );
        $this->assertResult( // test both?
            '<br clear="all" />',
            '<br style="clear:both;" />'
        );
        $this->assertResult(
            '<br clear="none" />',
            '<br style="clear:none;" />'
        );
        $this->assertResult(
            '<br clear="foo" />',
            '<br />'
        );
    }
    
    function testListTypeTransform() {
        // ul
        $this->assertResult(
            '<ul type="disc" />',
            '<ul style="list-style-type:disc;" />'
        );
        $this->assertResult(
            '<ul type="square" />',
            '<ul style="list-style-type:square;" />'
        );
        $this->assertResult(
            '<ul type="circle" />',
            '<ul style="list-style-type:circle;" />'
        );
        $this->assertResult( // case insensitive
            '<ul type="CIRCLE" />',
            '<ul style="list-style-type:circle;" />'
        );
        $this->assertResult(
            '<ul type="a" />',
            '<ul />'
        );
        // ol
        $this->assertResult(
            '<ol type="1" />',
            '<ol style="list-style-type:decimal;" />'
        );
        $this->assertResult(
            '<ol type="i" />',
            '<ol style="list-style-type:lower-roman;" />'
        );
        $this->assertResult(
            '<ol type="I" />',
            '<ol style="list-style-type:upper-roman;" />'
        );
        $this->assertResult(
            '<ol type="a" />',
            '<ol style="list-style-type:lower-alpha;" />'
        );
        $this->assertResult(
            '<ol type="A" />',
            '<ol style="list-style-type:upper-alpha;" />'
        );
        $this->assertResult(
            '<ol type="disc" />',
            '<ol />'
        );
        // li
        $this->assertResult(
            '<li type="circle" />',
            '<li style="list-style-type:circle;" />'
        );
        $this->assertResult(
            '<li type="A" />',
            '<li style="list-style-type:upper-alpha;" />'
        );
        $this->assertResult( // case sensitive
            '<li type="CIRCLE" />',
            '<li />'
        );
        
    }
    
}


