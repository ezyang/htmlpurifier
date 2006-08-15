<?php

require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/Generator.php';

class HTMLPurifier_ChildDefTest extends UnitTestCase
{
    
    var $def;
    var $lex;
    var $gen;
    
    function HTMLPurifier_ChildDefTest() {
        $this->lex = HTMLPurifier_Lexer::create();
        $this->gen = new HTMLPurifier_Generator();
        parent::UnitTestCase();
    }
    
    function assertSeries($inputs, $expect, $config, $context = array()) {
        foreach ($inputs as $i => $input) {
            
            if (!isset($context[$i])) {
                $context[$i] = null;
            }
            if (!isset($config[$i])) {
                $config[$i] = HTMLPurifier_Config::createDefault();
            }
            
            $tokens = $this->lex->tokenizeHTML($input, $config[$i]);
            $result = $this->def->validateChildren($tokens, $config[$i], $context[$i]);
            
            if (is_bool($expect[$i])) {
                $this->assertIdentical($expect[$i], $result, "Test $i: %s");
            } else {
                $result_html = $this->gen->generateFromTokens($result, $config[$i]);
                $this->assertIdentical($expect[$i], $result_html, "Test $i: %s");
                paintIf($result_html, $result_html != $expect[$i]);
            }
        }
    }
    
    function test_custom() {
        
        // the table definition
        $this->def = new HTMLPurifier_ChildDef_Custom(
            '(caption?, (col*|colgroup*), thead?, tfoot?, (tbody+|tr+))');
        
        $inputs = $expect = $config = array();
        
        $inputs[0] = '';
        $expect[0] = false;
        
        // we really don't care what's inside, because if it turns out
        // this tr is illegal, we'll end up re-evaluating the parent node
        // anyway.
        $inputs[1] = '<tr></tr>';
        $expect[1] = true;
        
        $inputs[2] = '<caption></caption><col></col><thead></thead>' .
                     '<tfoot></tfoot><tbody></tbody>';
        $expect[2] = true;
        
        $inputs[3] = '<col></col><col></col><col></col><tr></tr>';
        $expect[3] = true;
        
        $this->assertSeries($inputs, $expect, $config);
        
    }
    
    function test_parsing() {
        
        $def = new HTMLPurifier_ChildDef_Required('foobar | bang |gizmo');
        $this->assertEqual($def->elements,
          array(
            'foobar' => true
           ,'bang'   => true
           ,'gizmo'  => true
          ));
        
        $def = new HTMLPurifier_ChildDef_Required(array('href', 'src'));
        $this->assertEqual($def->elements,
          array(
            'href' => true
           ,'src'  => true
          ));
    }
    
    function test_required_pcdata_forbidden() {
        
        $this->def = new HTMLPurifier_ChildDef_Required('dt | dd');
        $inputs = $expect = $config = array();
        
        $inputs[0] = '';
        $expect[0] = false;
        
        $inputs[1] = '<dt>Term</dt>Text in an illegal location'.
                     '<dd>Definition</dd><b>Illegal tag</b>';
        
        $expect[1] = '<dt>Term</dt><dd>Definition</dd>';
        
        $inputs[2] = 'How do you do!';
        $expect[2] = false;
        
        // whitespace shouldn't trigger it
        $inputs[3] = "\n<dd>Definition</dd>       ";
        $expect[3] = true;
        
        $inputs[4] ='<dd>Definition</dd>       <b></b>       ';
        $expect[4] = '<dd>Definition</dd>              ';
        
        $inputs[5] = "\t      ";
        $expect[5] = false;
        
        $this->assertSeries($inputs, $expect, $config);
        
    }
    
    function test_required_pcdata_allowed() {
        $this->def = new HTMLPurifier_ChildDef_Required('#PCDATA | b');
        
        $inputs = $expect = $config = array();
        
        $inputs[0] = '<b>Bold text</b><img />';
        $expect[0] = '<b>Bold text</b>';
        
        // with child escaping on
        $inputs[1] = '<b>Bold text</b><img />';
        $expect[1] = '<b>Bold text</b>&lt;img /&gt;';
        $config[1] = HTMLPurifier_Config::createDefault();
        $config[1]->set('Core', 'EscapeInvalidChildren', true);
        
        $this->assertSeries($inputs, $expect, $config);
    }
    
    function test_optional() {
        $this->def = new HTMLPurifier_ChildDef_Optional('b | i');
        
        $inputs = $expect = $config = array();
        
        $inputs[0] = '<b>Bold text</b><img />';
        $expect[0] = '<b>Bold text</b>';
        
        $inputs[1] = 'Not allowed text';
        $expect[1] = '';
        
        $this->assertSeries($inputs, $expect, $config);
    }
    
    function test_chameleon() {
        
        $this->def = new HTMLPurifier_ChildDef_Chameleon(
            'b | i', // allowed only when in inline context
            'b | i | div' // allowed only when in block context
        );
        
        $inputs = $expect = $config = array();
        $context = array();
        
        $inputs[0] = '<b>Allowed.</b>';
        $expect[0] = true;
        $context[0] = 'inline';
        
        $inputs[1] = '<div>Not allowed.</div>';
        $expect[1] = '';
        $context[1] = 'inline';
        
        $inputs[2] = '<div>Allowed.</div>';
        $expect[2] = true;
        $context[2] = 'block';
        
        $this->assertSeries($inputs, $expect, $config, $context);
        
    }
    
}

?>