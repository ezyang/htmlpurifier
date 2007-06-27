<?php

require_once 'HTMLPurifier/ChildDefHarness.php';
require_once 'HTMLPurifier/ChildDef/StrictBlockquote.php';

class   HTMLPurifier_ChildDef_StrictBlockquoteTest
extends HTMLPurifier_ChildDefHarness
{
    
    function test() {
        
        $this->obj = new HTMLPurifier_ChildDef_StrictBlockquote('div | p');
        
        // assuming default wrap is p
        
        $this->assertResult('');
        $this->assertResult('<p>Valid</p>');
        $this->assertResult('<div>Still valid</div>');
        $this->assertResult('Needs wrap', '<p>Needs wrap</p>');
        $this->assertResult('<p>Do not wrap</p>    <p>Whitespace</p>');
        $this->assertResult(
               'Wrap'. '<p>Do not wrap</p>',
            '<p>Wrap</p><p>Do not wrap</p>'
        );
        $this->assertResult(
            '<p>Do not</p>'.'<b>Wrap</b>',
            '<p>Do not</p><p><b>Wrap</b></p>'
        );
        $this->assertResult(
            '<li>Not allowed</li>Paragraph.<p>Hmm.</p>',
            '<p>Not allowedParagraph.</p><p>Hmm.</p>'
        );
        $this->assertResult(
            $var = 'He said<br />perhaps<br />we should <b>nuke</b> them.',
            "<p>$var</p>"
        );
        $this->assertResult(
            '<foo>Bar</foo><bas /><b>People</b>Conniving.'. '<p>Fools!</p>',
              '<p>Bar'.          '<b>People</b>Conniving.</p><p>Fools!</p>'
        );
        
        $this->assertResult('Needs wrap', '<div>Needs wrap</div>',
            array('HTML.BlockWrapper' => 'div'));
        
    }
    
    function testError() {
        $this->obj = new HTMLPurifier_ChildDef_StrictBlockquote('div | p');
        $this->assertResult('Needs wrap', '<p>Needs wrap</p>',
            array('HTML.BlockWrapper' => 'dav'));
        $this->swallowErrors();
    }
    
}

