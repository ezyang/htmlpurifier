<?php

// this page is UTF-8 encoded!

require_once 'HTMLPurifier/EntityLookup.php';

class HTMLPurifier_EntityLookupTest extends HTMLPurifier_Harness
{
    
    function test() {
        
        $lookup = HTMLPurifier_EntityLookup::instance();
        
        // latin char
        $this->assertIdentical('â', $lookup->table['acirc']);
        
        // special char
        $this->assertIdentical('"', $lookup->table['quot']);
        $this->assertIdentical('“', $lookup->table['ldquo']);
        $this->assertIdentical('<', $lookup->table['lt']); //expressed strangely
        
        // symbol char
        $this->assertIdentical('θ', $lookup->table['theta']);
        
    }
    
}

