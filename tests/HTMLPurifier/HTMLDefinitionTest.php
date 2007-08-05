<?php

require_once 'HTMLPurifier/HTMLDefinition.php';

class HTMLPurifier_HTMLDefinitionTest extends HTMLPurifier_Harness
{
    
    function test_parseTinyMCEAllowedList() {
        
        $def = new HTMLPurifier_HTMLDefinition();
        
        // note: this is case-sensitive, but its config schema 
        // counterpart is not. This is generally a good thing for users,
        // but it's a slight internal inconsistency
        
        $this->assertEqual(
            $def->parseTinyMCEAllowedList(''),
            array(array(), array())
        );
        
        $this->assertEqual(
            $def->parseTinyMCEAllowedList('a,b,c'),
            array(array('a' => true, 'b' => true, 'c' => true), array())
        );
        
        $this->assertEqual(
            $def->parseTinyMCEAllowedList('a[x|y|z]'),
            array(array('a' => true), array('a.x' => true, 'a.y' => true, 'a.z' => true))
        );
        
        $this->assertEqual(
            $def->parseTinyMCEAllowedList('*[id]'),
            array(array(), array('*.id' => true))
        );
        
        $this->assertEqual(
            $def->parseTinyMCEAllowedList('a[*]'),
            array(array('a' => true), array('a.*' => true))
        );
        
        $this->assertEqual(
            $def->parseTinyMCEAllowedList('span[style],strong,a[href|title]'),
            array(array('span' => true, 'strong' => true, 'a' => true),
            array('span.style' => true, 'a.href' => true, 'a.title' => true))
        );
        
        $this->assertEqual(
            // alternate form:
            $def->parseTinyMCEAllowedList(
'span[style]
strong
a[href|title]
'),
            array(array('span' => true, 'strong' => true, 'a' => true),
            array('span.style' => true, 'a.href' => true, 'a.title' => true))
        );
        
    }
    
    function test_Allowed() {
        
        $config1 = HTMLPurifier_Config::create(array(
            'HTML.AllowedElements' => array('b', 'i', 'p', 'a'),
            'HTML.AllowedAttributes' => array('a.href', '*.id')
        ));
        
        $config2 = HTMLPurifier_Config::create(array(
            'HTML.Allowed' => 'b,i,p,a[href],*[id]'
        ));
        
        $this->assertEqual($config1->getHTMLDefinition(), $config2->getHTMLDefinition());
        
    }
    
    function test_addAttribute() {
        
        $config = HTMLPurifier_Config::create(array(
            'HTML.DefinitionID' => 'HTMLPurifier_HTMLDefinitionTest->test_addAttribute'
        ));
        $def =& $config->getHTMLDefinition(true);
        $def->addAttribute('span', 'custom', 'Enum#attribute');
        
        $purifier = new HTMLPurifier($config);
        $input = '<span custom="attribute">Custom!</span>';
        $output = $purifier->purify($input);
        $this->assertIdentical($input, $output);
        
    }
    
    function test_addElement() {
        
        $config = HTMLPurifier_Config::create(array(
            'HTML.DefinitionID' => 'HTMLPurifier_HTMLDefinitionTest->test_addElement'
        ));
        $def =& $config->getHTMLDefinition(true);
        $def->addElement('marquee', 'Inline', 'Inline', 'Common', array('width' => 'Length'));
        
        $purifier = new HTMLPurifier($config);
        $input = '<span><marquee width="50">Foobar</marquee></span>';
        $output = $purifier->purify($input);
        $this->assertIdentical($input, $output);
        
    }
    
}

