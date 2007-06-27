<?php

require_once 'HTMLPurifier/ChildDefHarness.php';
require_once 'HTMLPurifier/ChildDef/Table.php';

class HTMLPurifier_ChildDef_TableTest extends HTMLPurifier_ChildDefHarness
{
    
    function test() {
        
        $this->obj = new HTMLPurifier_ChildDef_Table();
        
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
        $this->assertResult("\n   <tr />\n  <tr />\n ", true, array('Output.Newline' => "\n"));
        $this->assertResult(
          "\n\t<tbody />\n\t\t<tfoot />\n\t\t\t",
          "\n\t\t<tfoot />\n\t<tbody />\n\t\t\t",
          array('Output.Newline' => "\n")
        );
        
    }
    
}

