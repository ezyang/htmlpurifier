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
    
    function test_substituteNonSpecialEntities() {
        $char_theta = $this->_entity_lookup->table['theta'];
        $this->assertIdentical($char_theta,
            $this->Encoder->substituteNonSpecialEntities('&theta;') );
        $this->assertIdentical('"',
            $this->Encoder->substituteNonSpecialEntities('"') );
        
        // numeric tests, adapted from Feyd
        $args = array();
        $args[] = array(1114112,false     );
        $args[] = array(1114111,'F48FBFBF'); // 0x0010FFFF
        $args[] = array(1048576,'F4808080'); // 0x00100000
        $args[] = array(1048575,'F3BFBFBF'); // 0x000FFFFF
        $args[] = array(262144, 'F1808080'); // 0x00040000
        $args[] = array(262143, 'F0BFBFBF'); // 0x0003FFFF
        $args[] = array(65536,  'F0908080'); // 0x00010000
        $args[] = array(65535,  'EFBFBF'  ); // 0x0000FFFF
        $args[] = array(57344,  'EE8080'  ); // 0x0000E000
        $args[] = array(57343,  false     ); // 0x0000DFFF  these are ill-formed
        $args[] = array(56040,  false     ); // 0x0000DAE8  these are ill-formed
        $args[] = array(55296,  false     ); // 0x0000D800  these are ill-formed
        $args[] = array(55295,  'ED9FBF'  ); // 0x0000D7FF
        $args[] = array(53248,  'ED8080'  ); // 0x0000D000
        $args[] = array(53247,  'ECBFBF'  ); // 0x0000CFFF
        $args[] = array(4096,   'E18080'  ); // 0x00001000
        $args[] = array(4095,   'E0BFBF'  ); // 0x00000FFF
        $args[] = array(2048,   'E0A080'  ); // 0x00000800
        $args[] = array(2047,   'DFBF'    ); // 0x000007FF
        $args[] = array(128,    'C280'    ); // 0x00000080  invalid SGML char
        $args[] = array(127,    '7F'      ); // 0x0000007F  invalid SGML char
        $args[] = array(0,      '00'      ); // 0x00000000  invalid SGML char

        $args[] = array(20108,  'E4BA8C'  ); // 0x00004E8C
        $args[] = array(77,     '4D'      ); // 0x0000004D
        $args[] = array(66306,  'F0908C82'); // 0x00010302
        $args[] = array(1072,   'D0B0'    ); // 0x00000430 
        
        foreach ($args as $arg) {
            $string = '&#' . $arg[0] . ';' . // decimal
                      '&#x' . dechex($arg[0]) . ';'; // hex
            $expect = '';
            if ($arg[1] !== false) {
                $chars = str_split($arg[1], 2);
                foreach ($chars as $char) {
                    $expect .= chr(hexdec($char));
                }
                $expect .= $expect; // double it
            }
            $this->assertIdentical(
                $this->Encoder->substituteNonSpecialEntities($string),
                $expect,
                $arg[0] . ': %s'
            );
        }
        
    }
    
    function test_specialEntityCallback() {
        
        $this->assertIdentical("'",$this->Encoder->specialEntityCallback(
            array('&#39;', null, '39', null) ));
    }
    
}

?>