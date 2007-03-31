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
    }
    
    function test() {
        
        // attribute order is VERY fragile, perhaps we should define
        // an ordering scheme!
        
        $this->assertResult('');
        
        // test ids
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
        
        // test classes
        $this->assertResult('<div class="valid">Valid</div>');
        
        $this->assertResult(
            '<div class="valid 0invalid">Keep valid.</div>',
            '<div class="valid">Keep valid.</div>'
        );
        
        // test title
        $this->assertResult(
            '<acronym title="PHP: Hypertext Preprocessor">PHP</acronym>'
        );
        
        // test lang
        $this->assertResult(
            '<span lang="fr">La soupe.</span>',
            '<span lang="fr" xml:lang="fr">La soupe.</span>'
        );
        
        // test align
        $this->assertResult(
            '<h1 align="center">Centered Headline</h1>',
            '<h1 style="text-align:center;">Centered Headline</h1>'
        );
        
        // test table
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
        
        // test URI
        $this->assertResult('<a href="http://www.google.com/">Google</a>');
        
        // test invalid URI
        $this->assertResult(
            '<a href="javascript:badstuff();">Google</a>',
            '<a>Google</a>'
        );
        
        // test required attributes for img
        
        // (this should never happen, as RemoveForeignElements
        //  should have removed the offending image tag)
        $this->assertResult(
            '<img />',
            '<img src="" alt="Invalid image" />'
        );
        
        $this->assertResult(
            '<img src="foobar.jpg" />',
            '<img src="foobar.jpg" alt="foobar.jpg" />'
        );
        
        $this->assertResult(
            '<img alt="pretty picture" />',
            '<img alt="pretty picture" src="" />'
        );
        
        // test required attributes for bdo
        $this->assertResult(
            '<bdo>Go left.</bdo>',
            '<bdo dir="ltr">Go left.</bdo>'
        );
        
        $this->assertResult(
            '<bdo dir="blahblah">Invalid value!</bdo>',
            '<bdo dir="ltr">Invalid value!</bdo>'
        );
        
        // see above, behavior is subtly different
        $this->assertResult(
            '<span dir="blahblah">Invalid value!</span>',
            '<span>Invalid value!</span>'
        );
        
        // test col.span is non-zero
        $this->assertResult(
            '<col span="0" />',
            '<col />'
        );
        
        // mailto in image is not allowed
        $this->assertResult(
            '<img src="mailto:foo@example.com" />',
            '<img src="" alt="Invalid image" />'
        );
        
        // name rewritten as id
        $this->assertResult(
            '<a name="foobar" />',
            '<a id="foobar" />',
            array('HTML.EnableAttrID' => true)
        );
        
        // lengths
        $this->assertResult(
            '<td height="10" width="5%" /><th height="5%" width="10" /><hr width="10" height="10" />',
            '<td style="height:10px;width:5%;" /><th style="height:5%;width:10px;" /><hr style="width:10px;" />'
        );
        
        // link types
        $this->assertResult(
            '<a href="foo" rel="nofollow" />',
            true,
            array('Attr.AllowedRel' => 'nofollow')
        );
        
    }
    
}

?>
