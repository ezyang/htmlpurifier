<?php

class ConfigDoc_DOM_DocumentTest extends UnitTestCase
{
    
    function testOverload() {
        $dom = new ConfigDoc_DOM_Document();
        $this->assertIsA($dom->createElement('a'), 'ConfigDoc_DOM_Element');
    }
    
}
