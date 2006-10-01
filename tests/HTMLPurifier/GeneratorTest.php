<?php

require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/EntityLookup.php';

require_once 'HTMLPurifier/Harness.php';

class HTMLPurifier_GeneratorTest extends HTMLPurifier_Harness
{
    
    var $gen;
    var $_entity_lookup;
    
    function HTMLPurifier_GeneratorTest() {
        $this->UnitTestCase();
        $this->gen = new HTMLPurifier_Generator();
        $this->_entity_lookup = HTMLPurifier_EntityLookup::instance();
    }
    
    function setUp() {
        $this->obj       = new HTMLPurifier_Generator();
        $this->func      = null;
        $this->to_tokens = false;
        $this->to_html   = false;
    }
    
    function test_generateFromToken() {
        
        $inputs = $expect = array();
        
        $inputs[0] = new HTMLPurifier_Token_Text('Foobar.<>');
        $expect[0] = 'Foobar.&lt;&gt;';
        
        $inputs[1] = new HTMLPurifier_Token_Start('a',
                            array('href' => 'dyn?a=foo&b=bar')
                         );
        $expect[1] = '<a href="dyn?a=foo&amp;b=bar">';
        
        $inputs[2] = new HTMLPurifier_Token_End('b');
        $expect[2] = '</b>';
        
        $inputs[3] = new HTMLPurifier_Token_Empty('br',
                            array('style' => 'font-family:"Courier New";')
                         );
        $expect[3] = '<br style="font-family:&quot;Courier New&quot;;" />';
        
        $inputs[4] = new HTMLPurifier_Token_Start('asdf');
        $expect[4] = '<asdf>';
        
        $inputs[5] = new HTMLPurifier_Token_Empty('br');
        $expect[5] = '<br />';
        
        // test fault tolerance
        $inputs[6] = null;
        $expect[6] = '';
        
        // don't convert non-special characters
        $theta_char = $this->_entity_lookup->table['theta'];
        $inputs[7] = new HTMLPurifier_Token_Text($theta_char);
        $expect[7] = $theta_char;
        
        foreach ($inputs as $i => $input) {
            $result = $this->obj->generateFromToken($input);
            $this->assertEqual($result, $expect[$i]);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_generateAttributes() {
        
        $inputs = $expect = array();
        
        $inputs[0] = array();
        $expect[0] = '';
        
        $inputs[1] = array('href' => 'dyn?a=foo&b=bar');
        $expect[1] = 'href="dyn?a=foo&amp;b=bar"';
        
        $inputs[2] = array('style' => 'font-family:"Courier New";');
        $expect[2] = 'style="font-family:&quot;Courier New&quot;;"';
        
        $inputs[3] = array('src' => 'picture.jpg', 'alt' => 'Short & interesting');
        $expect[3] = 'src="picture.jpg" alt="Short &amp; interesting"';
        
        // don't escape nonspecial characters
        $theta_char = $this->_entity_lookup->table['theta'];
        $inputs[4] = array('title' => 'Theta is ' . $theta_char);
        $expect[4] = 'title="Theta is ' . $theta_char . '"';
        
        foreach ($inputs as $i => $input) {
            $result = $this->obj->generateAttributes($input);
            $this->assertEqual($result, $expect[$i]);
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_generateFromTokens() {
        
        $this->func = 'generateFromTokens';
        
        $this->assertResult(
            array(
                new HTMLPurifier_Token_Start('b'),
                new HTMLPurifier_Token_Text('Foobar!'),
                new HTMLPurifier_Token_End('b')
            ),
            '<b>Foobar!</b>'
        );
        
        $this->assertResult(array(), '');
        
    }
    
    var $config;
    function assertGeneration($tokens, $expect) {
        $context = new HTMLPurifier_Context();
        $result = $this->gen->generateFromTokens(
          $tokens, $this->config, $context);
        // normalized newlines, this probably should be put somewhere else
        $result = str_replace("\r\n", "\n", $result);
        $result = str_replace("\r", "\n", $result);
        $this->assertEqual($expect, $result);
    }
    
    function test_generateFromTokens_XHTMLoff() {
        $this->config = HTMLPurifier_Config::createDefault();
        $this->config->set('Core', 'XHTML', false);
        
        // omit trailing slash
        $this->assertGeneration(
            array( new HTMLPurifier_Token_Empty('br') ),
            '<br>'
        );
        
        // there should be a test for attribute minimization, but it is
        // impossible for something like that to happen due to our current
        // definitions! fix it later
        
        // namespaced attributes must be dropped
        $this->assertGeneration(
            array( new HTMLPurifier_Token_Start('p', array('xml:lang'=>'fr')) ),
            '<p>'
        );
        
    }
    
    function test_generateFromTokens_TidyFormat() {
        // abort test if tidy isn't loaded
        if (!extension_loaded('tidy')) return;
        
        $this->config = HTMLPurifier_Config::createDefault();
        $this->config->set('Core', 'TidyFormat', true);
        
        // nice wrapping please
        $this->assertGeneration(
            array(
                new HTMLPurifier_Token_Start('div'),
                new HTMLPurifier_Token_Text('Text'),
                new HTMLPurifier_Token_End('div')
            ),
            "<div>\n  Text\n</div>\n"
        );
        
    }
    
}

?>