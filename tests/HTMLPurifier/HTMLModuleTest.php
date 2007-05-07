<?php

require_once 'HTMLPurifier/HTMLModule.php';
require_once 'HTMLPurifier/AttrDef.php';

class HTMLPurifier_HTMLModuleTest extends UnitTestCase
{
    
    function test_addElementToContentSet() {
        
        $module = new HTMLPurifier_HTMLModule();
        
        $module->addElementToContentSet('b', 'Inline');
        $this->assertIdentical($module->content_sets, array('Inline' => 'b'));
        
        $module->addElementToContentSet('i', 'Inline');
        $this->assertIdentical($module->content_sets, array('Inline' => 'b | i'));
        
    }
    
    function test_addElement() {
        
        $module = new HTMLPurifier_HTMLModule();
        $module->addElement(
            'a', true, 'Inline', 'Optional: #PCDATA', array('Common'),
            array(
                'href' => 'URI'
            )
        );
        
        $module2 = new HTMLPurifier_HTMLModule();
        $def = new HTMLPurifier_ElementDef();
        $def->safe = true;
        $def->content_model = '#PCDATA';
        $def->content_model_type = 'optional';
        $def->attr = array(
            'href' => 'URI',
            0 => array('Common')
        );
        $module2->info['a'] = $def;
        $module2->elements = array('a');
        $module2->content_sets['Inline'] = 'a';
        
        $this->assertIdentical($module, $module2);
        
    }
    
}

?>