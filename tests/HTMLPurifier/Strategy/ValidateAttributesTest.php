<?php

require_once('HTMLPurifier/Config.php');
require_once('HTMLPurifier/StrategyHarness.php');
require_once('HTMLPurifier/Strategy/ValidateAttributes.php');

class HTMLPurifier_Strategy_ValidateAttributesTest extends
      HTMLPurifier_StrategyHarness
{
    
    function test() {
        
        $strategy = new HTMLPurifier_Strategy_ValidateAttributes();
        
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
        $config[7]->attr_id_blacklist = array('invalid');
        
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
        
        // test align (won't work till CSS validation is fixed)
        // $inputs[12] = '<h1 align="center">Centered Headline</h1>';
        // $expect[12] = '<h1 style="text-align:center;">Centered Headline</h1>';
        
        $this->assertStrategyWorks($strategy, $inputs, $expect, $config);
        
    }
    
}

?>