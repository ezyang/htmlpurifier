<?php

require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/Generator.php';

class HTMLPurifier_ChildDefTest extends UnitTestCase
{
    
    var $lex;
    var $gen;
    
    function HTMLPurifier_ChildDefTest() {
        $this->lex = HTMLPurifier_Lexer::create();
        $this->gen = new HTMLPurifier_Generator();
        parent::UnitTestCase();
    }
    
    function assertSeries($inputs, $expect, $def) {
        foreach ($inputs as $i => $input) {
            $tokens = $this->lex->tokenizeHTML($input);
            $result = $def->validateChildren($tokens);
            if (is_bool($expect[$i])) {
                $this->assertIdentical($expect[$i], $result);
            } else {
                $result_html = $this->gen->generateFromTokens($result);
                $this->assertEqual($expect[$i], $result_html);
                paintIf($result_html, $result_html != $expect[$i]);
            }
        }
    }
    
    function test_complex() {
        
        // the table definition
        $def = new HTMLPurifier_ChildDef(
            '(caption?, (col*|colgroup*), thead?, tfoot?, (tbody+|tr+))');
        
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
        
        $this->assertSeries($inputs, $expect, $def);
        
    }
    
    function test_simple() {
        
        // simple is actually an abstract class
        // but we're unit testing some of the conv. functions it gives
        
        $def = new HTMLPurifier_ChildDef_Simple('foobar | bang |gizmo');
        $this->assertEqual($def->elements,
          array(
            'foobar' => true
           ,'bang'   => true
           ,'gizmo'  => true
          ));
        
        $def = new HTMLPurifier_ChildDef_Simple(array('href', 'src'));
        $this->assertEqual($def->elements,
          array(
            'href' => true
           ,'src'  => true
          ));
    }
    
    function test_required_pcdata_forbidden() {
        
        $def = new HTMLPurifier_ChildDef_Required('dt | dd');
        
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
        
        $this->assertSeries($inputs, $expect, $def);
        
    }
    
    function test_required_pcdata_allowed() {
        $def = new HTMLPurifier_ChildDef_Required('#PCDATA | b');
        
        $inputs[0] = '<b>Bold text</b><img />';
        $expect[0] = '<b>Bold text</b>&lt;img /&gt;';
        
        $this->assertSeries($inputs, $expect, $def);
    }
    
    function test_optional() {
        $def = new HTMLPurifier_ChildDef_Optional('b | i');
        
        $inputs[0] = '<b>Bold text</b><img />';
        $expect[0] = '<b>Bold text</b>';
        
        $inputs[1] = 'Not allowed text';
        $expect[1] = '';
        
        $this->assertSeries($inputs, $expect, $def);
    }
    
}

?>