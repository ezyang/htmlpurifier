<?php

require_once 'HTMLPurifier/Harness.php';

require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/Lexer/DirectLex.php';
require_once 'HTMLPurifier/Generator.php';

class HTMLPurifier_ChildDefTest extends HTMLPurifier_Harness
{
    
    function setUp() {
        $this->obj       = null;
        $this->func      = 'validateChildren';
        $this->to_tokens = true;
        $this->to_html   = true;
    }
    
    function test_custom() {
        
        $this->obj = new HTMLPurifier_ChildDef_Custom('(a,b?,c*,d+,(a,b)*)');
        
        $this->assertResult('', false);
        $this->assertResult('<a /><a />', false);
        
        $this->assertResult('<a /><b /><c /><d /><a /><b />');
        $this->assertResult('<a /><d>Dob</d><a /><b>foo</b>'.
          '<a href="moo" /><b>foo</b>');
        
    }
    
    function test_table() {
        
        // the table definition
        $this->obj = new HTMLPurifier_ChildDef_Table();
        
        $inputs = $expect = $config = array();
        
        $this->assertResult('', false);
        
        // we're using empty tags to compact the tests: under real circumstances
        // there would be contents in them
        
        $this->assertResult('<tr />');
        $this->assertResult('<caption /><col /><thead /><tfoot /><tbody>'.
            '<tr><td>asdf</td></tr></tbody>');
        $this->assertResult('<col /><col /><col /><tr />');
        
        // mixed up order
        $this->assertResult(
          '<col /><colgroup /><tbody /><tfoot /><thead /><tr>1</tr><caption /><tr />',
          '<caption /><col /><colgroup /><thead /><tfoot /><tbody /><tr>1</tr><tr />');
        
        // duplicates of singles
        // - first caption serves
        // - trailing tfoots/theads get turned into tbodys
        $this->assertResult(
          '<caption>1</caption><caption /><tbody /><tbody /><tfoot>1</tfoot><tfoot />',
          '<caption>1</caption><tfoot>1</tfoot><tbody /><tbody /><tbody />'
        );
        
        // errant text dropped (until bubbling is implemented)
        $this->assertResult('foo', false);
        
        // whitespace sticks to the previous element, last whitespace is
        // stationary
        $this->assertResult("\n   <tr />\n  <tr />\n ");
        $this->assertResult(
          "\n\t<tbody />\n\t\t<tfoot />\n\t\t\t",
          "\n\t\t<tfoot />\n\t<tbody />\n\t\t\t"
        );
        
    }
    
    function testParsing() {
        
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
        
        $this->obj = new HTMLPurifier_ChildDef_Required('dt | dd');
        
        $this->assertResult('', false);
        $this->assertResult(
          '<dt>Term</dt>Text in an illegal location'.
             '<dd>Definition</dd><b>Illegal tag</b>',
          '<dt>Term</dt><dd>Definition</dd>');
        $this->assertResult('How do you do!', false);
        
        // whitespace shouldn't trigger it
        $this->assertResult("\n<dd>Definition</dd>       ");
        
        $this->assertResult(
          '<dd>Definition</dd>       <b></b>       ',
          '<dd>Definition</dd>              '
        );
        $this->assertResult("\t      ", false);
        
    }
    
    function test_required_pcdata_allowed() {
        
        $this->obj = new HTMLPurifier_ChildDef_Required('#PCDATA | b');
        
        $this->assertResult('<b>Bold text</b><img />', '<b>Bold text</b>');
        
        // with child escaping on
        $this->assertResult(
            '<b>Bold text</b><img />',
            '<b>Bold text</b>&lt;img /&gt;',
            array(
              'Core.EscapeInvalidChildren' => true
            )
        );
        
    }
    
    function test_optional() {
        
        $this->obj = new HTMLPurifier_ChildDef_Optional('b | i');
        
        $this->assertResult('<b>Bold text</b><img />', '<b>Bold text</b>');
        $this->assertResult('Not allowed text', '');
        
    }
    
    function test_chameleon() {
        
        $this->obj = new HTMLPurifier_ChildDef_Chameleon(
            'b | i',      // allowed only when in inline context
            'b | i | div' // allowed only when in block context
        );
        
        $this->assertResult(
            '<b>Allowed.</b>', true,
            array(), array('ParentType' => 'inline')
        );
        
        $this->assertResult(
            '<div>Not allowed.</div>', '',
            array(), array('ParentType' => 'inline')
        );
        
        $this->assertResult(
            '<div>Allowed.</div>', true,
            array(), array('ParentType' => 'block')
        );
        
    }
    
}

?>
