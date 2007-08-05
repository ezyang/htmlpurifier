<?php

require_once 'HTMLPurifier/Encoder.php';

class HTMLPurifier_EncoderTest extends HTMLPurifier_Harness
{
    
    var $_entity_lookup;
    
    function setUp() {
        $this->_entity_lookup = HTMLPurifier_EntityLookup::instance();
    }
    
    function assertCleanUTF8($string, $expect = null) {
        if ($expect === null) $expect = $string;
        $this->assertIdentical(HTMLPurifier_Encoder::cleanUTF8($string), $expect, 'iconv: %s');
        $this->assertIdentical(HTMLPurifier_Encoder::cleanUTF8($string, true), $expect, 'PHP: %s');
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
        $context = new HTMLPurifier_Context();
        
        // UTF-8 means that we don't touch it
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertToUTF8("\xF6", $config, $context),
            "\xF6" // this is invalid
        );
        $this->assertNoErrors();
        
        $config = HTMLPurifier_Config::create(array(
            'Core.Encoding' => 'ISO-8859-1'
        ));
        
        // Now it gets converted
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertToUTF8("\xF6", $config, $context),
            "\xC3\xB6"
        );
        
        $config = HTMLPurifier_Config::create(array(
            'Core.Encoding' => 'ISO-8859-1',
            'Test.ForceNoIconv' => true
        ));
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertToUTF8("\xF6", $config, $context),
            "\xC3\xB6"
        );
        
    }
    
    function test_convertFromUTF8() {
        $config = HTMLPurifier_Config::createDefault();
        $context = new HTMLPurifier_Context();
        
        // zhong-wen
        $chinese = "\xE4\xB8\xAD\xE6\x96\x87 (Chinese)";
        
        // UTF-8 means that we don't touch it
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertFromUTF8("\xC3\xB6", $config, $context),
            "\xC3\xB6"
        );
        
        $config = HTMLPurifier_Config::create(array(
            'Core.Encoding' => 'ISO-8859-1'
        ));
        
        // Now it gets converted
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertFromUTF8("\xC3\xB6", $config, $context),
            "\xF6"
        );
        
        if (function_exists('iconv')) {
            // iconv has it's own way
            $this->assertIdentical(
                HTMLPurifier_Encoder::convertFromUTF8($chinese, $config, $context),
                " (Chinese)"
            );
        }
        
        // Plain PHP implementation has slightly different behavior
        $config = HTMLPurifier_Config::create(array(
            'Core.Encoding' => 'ISO-8859-1',
            'Test.ForceNoIconv' => true
        ));
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertFromUTF8("\xC3\xB6", $config, $context),
            "\xF6"
        );
        
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertFromUTF8($chinese, $config, $context),
            "?? (Chinese)"
        );
        
        // Preserve the characters!
        $config = HTMLPurifier_Config::create(array(
            'Core.Encoding' => 'ISO-8859-1',
            'Core.EscapeNonASCIICharacters' => true
        ));
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertFromUTF8($chinese, $config, $context),
            "&#20013;&#25991; (Chinese)"
        );
        
    }
    
    function test_convertToASCIIDumbLossless() {
        
        // Uppercase thorn letter
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertToASCIIDumbLossless("\xC3\x9Eorn"),
            "&#222;orn"
        );
        
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertToASCIIDumbLossless("an"),
            "an"
        );
        
        // test up to four bytes
        $this->assertIdentical(
            HTMLPurifier_Encoder::convertToASCIIDumbLossless("\xF3\xA0\x80\xA0"),
            "&#917536;"
        );
        
    }
    
}

