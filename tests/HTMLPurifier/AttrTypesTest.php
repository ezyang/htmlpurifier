<?php

require_once 'HTMLPurifier/AttrTypes.php';

class HTMLPurifier_AttrTypesTest extends UnitTestCase
{
    
    function test_get() {
        $types = new HTMLPurifier_AttrTypes();
        
        $this->assertIdentical(
            $types->get('CDATA'),
            $types->info['CDATA']
        );
        
        $this->expectError('Cannot retrieve undefined attribute type foobar');
        $types->get('foobar');
        
    }
    
}

?>