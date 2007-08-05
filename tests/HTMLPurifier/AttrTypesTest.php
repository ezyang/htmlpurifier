<?php

require_once 'HTMLPurifier/AttrTypes.php';

class HTMLPurifier_AttrTypesTest extends HTMLPurifier_Harness
{
    
    function test_get() {
        $types = new HTMLPurifier_AttrTypes();
        
        $this->assertIdentical(
            $types->get('CDATA'),
            $types->info['CDATA']
        );
        
        $this->expectError('Cannot retrieve undefined attribute type foobar');
        $types->get('foobar');
        
        $this->assertIdentical(
            $types->get('Enum#foo,bar'),
            new HTMLPurifier_AttrDef_Enum(array('foo', 'bar'))
        );
        
    }
    
}

