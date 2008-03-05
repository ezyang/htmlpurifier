<?php

class HTMLPurifier_VarParser_NativeTest extends HTMLPurifier_VarParserHarness
{
    
    public function testValidateSimple() {
        $this->assertValid('"foo\\\\"', 'string', 'foo\\');
    }
    
}
