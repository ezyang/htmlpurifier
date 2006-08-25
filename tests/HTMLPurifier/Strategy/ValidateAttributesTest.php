<?php

require_once('HTMLPurifier/Config.php');
require_once('HTMLPurifier/StrategyHarness.php');
require_once('HTMLPurifier/Strategy/ValidateAttributes.php');

class HTMLPurifier_Strategy_ValidateAttributesTest extends
      HTMLPurifier_StrategyHarness
{
    
    function test() {
        
        $strategy = new HTMLPurifier_Strategy_ValidateAttributes();
        
        // attribute order is VERY fragile, perhaps we should define
        // an ordering scheme!
        
        $inputs = array();
        $expect = array();
        $config = array();
        
        $inputs[0] = '';
        $expect[0] = '';
        
        // test ids
        
        $inputs[1] = '<div id="valid">Preserve the ID.</div>';
        $expect[1] = $inputs[1];
        
        $inputs[2] = '<div id="0invalid">Kill the ID.</div>';
        $expect[2] = '<div>Kill the ID.</div>';
        
        // test id accumulator
        $inputs[3] = '<div id="valid">Valid</div><div id="valid">Invalid</div>';
        $expect[3] = '<div id="valid">Valid</div><div>Invalid</div>';
        
        $inputs[4] = '<span dir="up-to-down">Bad dir.</span>';
        $expect[4] = '<span>Bad dir.</span>';
        
        // test attribute case sensitivity
        $inputs[5] = '<div ID="valid">Convert ID to lowercase.</div>';
        $expect[5] = '<div id="valid">Convert ID to lowercase.</div>';
        
        // test simple attribute substitution
        $inputs[6] = '<div id=" valid ">Trim whitespace.</div>';
        $expect[6] = '<div id="valid">Trim whitespace.</div>';
        
        // test configuration id blacklist
        $inputs[7] = '<div id="invalid">Invalid</div>';
        $expect[7] = '<div>Invalid</div>';
        $config[7] = HTMLPurifier_Config::createDefault();
        $config[7]->set('Attr', 'IDBlacklist', array('invalid'));
        
        // test classes
        $inputs[8] = '<div class="valid">Valid</div>';
        $expect[8] = $inputs[8];
        
        $inputs[9] = '<div class="valid 0invalid">Keep valid.</div>';
        $expect[9] = '<div class="valid">Keep valid.</div>';
        
        // test title
        $inputs[10] = '<acronym title="PHP: Hypertext Preprocessor">PHP</acronym>';
        $expect[10] = $inputs[10];
        
        // test lang
        $inputs[11] = '<span lang="fr">La soupe.</span>';
        $expect[11] = '<span lang="fr" xml:lang="fr">La soupe.</span>';
        
        // test align (won't work till CSS validation is implemented)
        $inputs[12] = '<h1 align="center">Centered Headline</h1>';
        $expect[12] = '<h1 style="text-align:center;">Centered Headline</h1>';
        
        // test table
        $inputs[13] = 
        
'<table frame="above" rules="rows" summary="A test table" border="2" cellpadding="5%" cellspacing="3" width="100%">
    <col align="right" width="4*" />
    <col charoff="5" align="char" width="1*" />
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
</table>';
        
        $expect[13] = $inputs[13];
        
        // test URI
        $inputs[14] = '<a href="http://www.google.com/">Google</a>';
        $expect[14] = $inputs[14];
        
        // test invalid URI
        $inputs[15] = '<a href="javascript:badstuff();">Google</a>';
        $expect[15] = '<a>Google</a>';
        
        // test required attributes for img
        $inputs[16] = '<img />';
        $expect[16] = '<img src="" alt="Invalid image" />';
        
        $inputs[17] = '<img src="foobar.jpg" />';
        $expect[17] = '<img src="foobar.jpg" alt="foobar.jpg" />';
        
        $inputs[18] = '<img alt="pretty picture" />';
        $expect[18] = '<img alt="pretty picture" src="" />';
        
        // test required attributes for bdo
        $inputs[19] = '<bdo>Go left.</bdo>';
        $expect[19] = '<bdo dir="ltr">Go left.</bdo>';
        
        $inputs[20] = '<bdo dir="blahblah">Invalid value!</bdo>';
        $expect[20] = '<bdo dir="ltr">Invalid value!</bdo>';
        
        // comparison check for test 20
        $inputs[21] = '<span dir="blahblah">Invalid value!</span>';
        $expect[21] = '<span>Invalid value!</span>';
        
        // test col.span is non-zero
        $inputs[22] = '<col span="0" />';
        $expect[22] = '<col />';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect, $config);
        
    }
    
}

?>