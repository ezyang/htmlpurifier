<?php

require_once 'HTMLPurifier/Encoder.php';

class HTMLPurifier_EncoderTest extends UnitTestCase
{
    
    var $Encoder;
    
    function setUp() {
        $this->Encoder = new HTMLPurifier_Encoder();
        $this->_entity_lookup = HTMLPurifier_EntityLookup::instance();
    }
    
    function assertCleanUTF8($string, $expect = null) {
        if ($expect === null) $expect = $string;
        $this->assertIdentical($this->Encoder->cleanUTF8($string), $expect);
        $this->assertIdentical($this->Encoder->cleanUTF8($string, true), $expect);
    }
    
    function test_cleanUTF8() {
        $this->assertCleanUTF8('Normal string.');
        $this->assertCleanUTF8("Test\tAllowed\nControl\rCharacters");
        $this->assertCleanUTF8("null byte: \0", 'null byte: ');
        $this->assertCleanUTF8("\1\2\3\4\5\6\7", '');
        $this->assertCleanUTF8("\x7F", ''); // one byte invalid SGML char
        $this->assertCleanUTF8("\xC2\x80", ''); // two byte invalid SGML
        $this->assertCleanUTF8("\xF3\xBF\xBF\xBF"); // valid four byte
        $this->assertCleanUTF8("\xDF\xFF", ''); // malformed UTF8
    }
    
    function test_convertToUTF8() {
        $config = HTMLPurifier_Config::createDefault();
        
        // UTF-8 means that we don't touch it
        $this->assertIdentical(
            $this->Encoder->convertToUTF8("\xF6", $config),
            "\xF6" // this is invalid
        );
        $this->assertNoErrors();
        
        $config->set('Core', 'Encoding', 'ISO-8859-1');
        
        // Now it gets converted
        $this->assertIdentical(
            $this->Encoder->convertToUTF8("\xF6", $config),
            "\xC3\xB6"
        );
        
        $config->set('Test', 'ForceNoIconv', true);
        
        $this->assertIdentical(
            $this->Encoder->convertToUTF8("\xF6", $config),
            "\xC3\xB6"
        );
        
    }
    
    function test_convertFromUTF8() {
        $config = HTMLPurifier_Config::createDefault();
        
        // UTF-8 means that we don't touch it
        $this->assertIdentical(
            $this->Encoder->convertFromUTF8("\xC3\xB6", $config),
            "\xC3\xB6"
        );
        
        $config->set('Core', 'Encoding', 'ISO-8859-1');
        
        // Now it gets converted
        $this->assertIdentical(
            $this->Encoder->convertFromUTF8("\xC3\xB6", $config),
            "\xF6"
        );
        
        $config->set('Test', 'ForceNoIconv', true);
        
        $this->assertIdentical(
            $this->Encoder->convertFromUTF8("\xC3\xB6", $config),
            "\xF6"
        );
        
    }
    
}

?>