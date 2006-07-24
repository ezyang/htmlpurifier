<?php

require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/Lexer/DirectLex.php';

class HTMLPurifier_DefinitionTest extends UnitTestCase
{
    
    var $def, $lex, $gen;
    
    function HTMLPurifier_DefinitionTest() {
        $this->UnitTestCase();
        $this->def = new HTMLPurifier_Definition();
        $this->def->loadData();
        
        // we can't use the DOM lexer since it does too much stuff
        // automatically, however, we should be able to use it
        // interchangeably if we wanted to...
        
        if (true) {
            $this->lex = new HTMLPurifier_Lexer_DirectLex();
        } else {
            require_once 'HTMLPurifier/Lexer/DOMLex.php';
            $this->lex = new HTMLPurifier_Lexer_DOMLex();
        }
        
        $this->gen = new HTMLPurifier_Generator();
    }
    
    function test_removeForeignElements() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = '';
        $expect[0] = $inputs[0];
        
        $inputs[1] = 'This is <b>bold text</b>.';
        $expect[1] = $inputs[1];
        
        // [INVALID]
        $inputs[2] = '<asdf>Bling</asdf><d href="bang">Bong</d><foobar />';
        $expect[2] = htmlspecialchars($inputs[2]);
        
        foreach ($inputs as $i => $input) {
            $tokens = $this->lex->tokenizeHTML($input);
            $result_tokens = $this->def->removeForeignElements($tokens);
            $result = $this->gen->generateFromTokens($result_tokens);
            $this->assertEqual($expect[$i], $result, "Test $i: %s");
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_makeWellFormed() {
        
        $inputs = array();
        $expect = array();
        
        $inputs[0] = '';
        $expect[0] = $inputs[0];
        
        $inputs[1] = 'This is <b>bold text</b>.';
        $expect[1] = $inputs[1];
        
        $inputs[2] = '<b>Unclosed tag, gasp!';
        $expect[2] = '<b>Unclosed tag, gasp!</b>';
        
        $inputs[3] = '<b><i>Bold and italic?</b>';
        $expect[3] = '<b><i>Bold and italic?</i></b>';
        
        // CHANGE THIS BEHAVIOR!
        $inputs[4] = 'Unused end tags... recycle!</b>';
        $expect[4] = 'Unused end tags... recycle!&lt;/b&gt;';
        
        $inputs[5] = '<br style="clear:both;">';
        $expect[5] = '<br style="clear:both;" />';
        
        $inputs[6] = '<div style="clear:both;" />';
        $expect[6] = '<div style="clear:both;"></div>';
        
        // test automatic paragraph closing
        
        $inputs[7] = '<p>Paragraph 1<p>Paragraph 2';
        $expect[7] = '<p>Paragraph 1</p><p>Paragraph 2</p>';
        
        $inputs[8] = '<div><p>Paragraphs<p>In<p>A<p>Div</div>';
        $expect[8] = '<div><p>Paragraphs</p><p>In</p><p>A</p><p>Div</p></div>';
        
        // automatic list closing
        
        $inputs[9] = '<ol><li>Item 1<li>Item 2</ol>';
        $expect[9] = '<ol><li>Item 1</li><li>Item 2</li></ol>';
        
        foreach ($inputs as $i => $input) {
            $tokens = $this->lex->tokenizeHTML($input);
            $result_tokens = $this->def->makeWellFormed($tokens);
            $result = $this->gen->generateFromTokens($result_tokens);
            $this->assertEqual($expect[$i], $result, "Test $i: %s");
            paintIf($result, $result != $expect[$i]);
        }
        
    }
    
    function test_fixNesting() {
        $inputs = array();
        $expect = array();
        
        // next id = 4
        
        // legal inline nesting
        $inputs[0] = '<b>Bold text</b>';
        $expect[0] = $inputs[0];
        
        // legal inline and block
        // as the parent element is considered FLOW
        $inputs[1] = '<a href="about:blank">Blank</a><div>Block</div>';
        $expect[1] = $inputs[1];
        
        // illegal block in inline, element -> text
        $inputs[2] = '<b><div>Illegal div.</div></b>';
        $expect[2] = '<b>&lt;div&gt;Illegal div.&lt;/div&gt;</b>';
        
        // test of empty set that's required, resulting in removal of node
        $inputs[3] = '<ul></ul>';
        $expect[3] = '';
        
        // test illegal text which gets removed
        $inputs[4] = '<ul>Illegal text<li>Legal item</li></ul>';
        $expect[4] = '<ul><li>Legal item</li></ul>';
        
        foreach ($inputs as $i => $input) {
            $tokens = $this->lex->tokenizeHTML($input);
            $result_tokens = $this->def->fixNesting($tokens);
            $result = $this->gen->generateFromTokens($result_tokens);
            $this->assertEqual($expect[$i], $result, "Test $i: %s");
            paintIf($result, $result != $expect[$i]);
        }
    }
    
}

?>