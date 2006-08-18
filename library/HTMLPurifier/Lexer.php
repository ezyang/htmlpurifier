<?php

require_once 'HTMLPurifier/Token.php';

HTMLPurifier_ConfigDef::define(
    'Core', 'AcceptFullDocuments', true,
    'This parameter determines whether or not the filter should accept full '.
    'HTML documents, not just HTML fragments.  When on, it will '.
    'drop all sections except the content between body.  Depending on '.
    'the implementation in use, this may speed up document parse times.'
);

/**
 * Forgivingly lexes HTML (SGML-style) markup into tokens.
 * 
 * A lexer parses a string of SGML-style markup and converts them into
 * corresponding tokens.  It doesn't check for well-formedness, although its
 * internal mechanism may make this automatic (such as the case of
 * HTMLPurifier_Lexer_DOMLex).  There are several implementations to choose
 * from.
 * 
 * A lexer is HTML-oriented: it might work with XML, but it's not
 * recommended, as we adhere to a subset of the specification for optimization
 * reasons.
 * 
 * This class should not be directly instantiated, but you may use create() to
 * retrieve a default copy of the lexer.  Being a supertype, this class
 * does not actually define any implementation, but offers commonly used
 * convenience functions for subclasses.
 * 
 * @note The unit tests will instantiate this class for testing purposes, as
 *       many of the utility functions require a class to be instantiated.
 *       Be careful when porting this class to PHP 5.
 * 
 * @par
 * 
 * @note
 * We use tokens rather than create a DOM representation because DOM would:
 * 
 * @par
 *  -# Require more processing power to create,
 *  -# Require recursion to iterate,
 *  -# Must be compatible with PHP 5's DOM (otherwise duplication),
 *  -# Has the entire document structure (html and body not needed), and
 *  -# Has unknown readability improvement.
 * 
 * @par
 * What the last item means is that the functions for manipulating tokens are
 * already fairly compact, and when well-commented, more abstraction may not
 * be needed.
 * 
 * @see HTMLPurifier_Token
 */
class HTMLPurifier_Lexer
{
    
    /**
     * Lexes an HTML string into tokens.
     * 
     * @param $string String HTML.
     * @return HTMLPurifier_Token array representation of HTML.
     */
    function tokenizeHTML($string, $config = null) {
        trigger_error('Call to abstract class', E_USER_ERROR);
    }
    
    /**
     * Retrieves or sets the default Lexer as a Prototype Factory.
     * 
     * Depending on what PHP version you are running, the abstract base
     * Lexer class will determine which concrete Lexer is best for you:
     * HTMLPurifier_Lexer_DirectLex for PHP 4, and HTMLPurifier_Lexer_DOMLex
     * for PHP 5 and beyond.
     * 
     * Passing the optional prototype lexer parameter will override the
     * default with your own implementation.  A copy/reference of the prototype
     * lexer will now be returned when you request a new lexer.
     * 
     * @note
     * Though it is possible to call this factory method from subclasses,
     * such usage is not recommended.
     * 
     * @param $prototype Optional prototype lexer.
     * @return Concrete lexer.
     */
    function create($prototype = null) {
        // we don't really care if it's a reference or a copy
        static $lexer = null;
        if ($prototype) {
            $lexer = $prototype;
        }
        if (empty($lexer)) {
            if (version_compare(PHP_VERSION, '5', '>=')) {
                require_once 'HTMLPurifier/Lexer/DOMLex.php';
                $lexer = new HTMLPurifier_Lexer_DOMLex();
            } else {
                require_once 'HTMLPurifier/Lexer/DirectLex.php';
                $lexer = new HTMLPurifier_Lexer_DirectLex();
            }
        }
        return $lexer;
    }
    
    /**
     * Decimal to parsed string conversion table for special entities.
     * @protected
     */
    var $_special_dec2str =
            array(
                    34 => '"',
                    38 => '&',
                    39 => "'",
                    60 => '<',
                    62 => '>'
            );
    
    /**
     * Stripped entity names to decimal conversion table for special entities.
     * @protected
     */
    var $_special_ent2dec =
            array(
                    'quot' => 34,
                    'amp'  => 38,
                    'lt'   => 60,
                    'gt'   => 62
            );
    
    /**
     * Most common entity to raw value conversion table for special entities.
     * @protected
     */
    var $_special_entity2str =
            array(
                    '&quot;' => '"',
                    '&amp;'  => '&',
                    '&lt;'   => '<',
                    '&gt;'   => '>',
                    '&#39;'  => "'",
                    '&#039;' => "'",
                    '&#x27;' => "'"
            );
    
    /**
     * Callback regex string for parsing entities.
     * @protected
     */                             
    var $_substituteEntitiesRegex =
'/&(?:[#]x([a-fA-F0-9]+)|[#]0*(\d+)|([A-Za-z]+));?/';
//     1. hex             2. dec      3. string
    
    /**
     * Substitutes non-special entities with their parsed equivalents. Since
     * running this whenever you have parsed character is t3h 5uck, we run
     * it before everything else.
     * 
     * @protected
     * @param $string String to have non-special entities parsed.
     * @returns Parsed string.
     */
    function substituteNonSpecialEntities($string) {
        // it will try to detect missing semicolons, but don't rely on it
        return preg_replace_callback(
            $this->_substituteEntitiesRegex,
            array($this, 'nonSpecialEntityCallback'),
            $string
            );
    }
    
    /**
     * Callback function for substituteNonSpecialEntities() that does the work.
     * 
     * @warning Though this is public in order to let the callback happen,
     *          calling it directly is not recommended.
     * @note Based on Feyd's function at
     *       <http://forums.devnetwork.net/viewtopic.php?p=191404#191404>,
     *       which is in public domain.
     * @note While we're going to do code point parsing anyway, a good
     *       optimization would be to refuse to translate code points that
     *       are non-SGML characters.  However, this could lead to duplication.
     * @param $matches  PCRE matches array, with 0 the entire match, and
     *                  either index 1, 2 or 3 set with a hex value, dec value,
     *                  or string (respectively).
     * @returns Replacement string.
     * @todo Implement string translations
     */
    
    // +----------+----------+----------+----------+
    // | 33222222 | 22221111 | 111111   |          |
    // | 10987654 | 32109876 | 54321098 | 76543210 | bit
    // +----------+----------+----------+----------+
    // |          |          |          | 0xxxxxxx | 1 byte 0x00000000..0x0000007F
    // |          |          | 110yyyyy | 10xxxxxx | 2 byte 0x00000080..0x000007FF
    // |          | 1110zzzz | 10yyyyyy | 10xxxxxx | 3 byte 0x00000800..0x0000FFFF
    // | 11110www | 10wwzzzz | 10yyyyyy | 10xxxxxx | 4 byte 0x00010000..0x0010FFFF
    // +----------+----------+----------+----------+
    // | 00000000 | 00011111 | 11111111 | 11111111 | Theoretical upper limit of legal scalars: 2097151 (0x001FFFFF)
    // | 00000000 | 00010000 | 11111111 | 11111111 | Defined upper limit of legal scalar codes
    // +----------+----------+----------+----------+ 
    
    function nonSpecialEntityCallback($matches) {
        // replaces all but big five
        $entity = $matches[0];
        $is_num = (@$matches[0][1] === '#');
        if ($is_num) {
            $is_hex = (@$entity[2] === 'x');
            $code = $is_hex ? hexdec($matches[1]) : (int) $matches[2];
            
            // abort for special characters
            if (isset($this->_special_dec2str[$code]))  return $entity;
            
            if($code > 1114111 or $code < 0 or
              ($code >= 55296 and $code <= 57343) ) {
                // bits are set outside the "valid" range as defined
                // by UNICODE 4.1.0 
                return '';
            }
            
            $x = $y = $z = $w = 0; 
            if ($code < 128) {
                // regular ASCII character
                $x = $code;
            } else {
                // set up bits for UTF-8
                $x = ($code & 63) | 128;
                if ($code < 2048) {
                    $y = (($code & 2047) >> 6) | 192;
                } else {
                    $y = (($code & 4032) >> 6) | 128;
                    if($code < 65536) {
                        $z = (($code >> 12) & 15) | 224;
                    } else {
                        $z = (($code >> 12) & 63) | 128;
                        $w = (($code >> 18) & 7)  | 240;
                    }
                } 
            }
            // set up the actual character
            $ret = '';
            if($w) $ret .= chr($w);
            if($z) $ret .= chr($z);
            if($y) $ret .= chr($y);
            $ret .= chr($x); 
            
            return $ret;
        } else {
            if (isset($this->_special_ent2dec[$matches[3]])) return $entity;
            if (!$this->_entity_lookup) {
                require_once 'HTMLPurifier/EntityLookup.php';
                $this->_entity_lookup = HTMLPurifier_EntityLookup::instance();
            }
            if (isset($this->_entity_lookup->table[$matches[3]])) {
                return $this->_entity_lookup->table[$matches[3]];
            } else {
                return $entity;
            }
        }
    }
    
    /**
     * Contains a copy of the EntityLookup table.
     * @protected
     */
    var $_entity_lookup;
    
    /**
     * Translates CDATA sections into regular sections (through escaping).
     * 
     * @protected
     * @param $string HTML string to process.
     * @returns HTML with CDATA sections escaped.
     */
    function escapeCDATA($string) {
        return preg_replace_callback(
            '/<!\[CDATA\[(.+?)\]\]>/',
            array('HTMLPurifier_Lexer', 'CDATACallback'),
            $string
        );
    }
    
    /**
     * Callback function for escapeCDATA() that does the work.
     * 
     * @warning Though this is public in order to let the callback happen,
     *          calling it directly is not recommended.
     * @params $matches PCRE matches array, with index 0 the entire match
     *                  and 1 the inside of the CDATA section.
     * @returns Escaped internals of the CDATA section.
     */
    function CDATACallback($matches) {
        // not exactly sure why the character set is needed, but whatever
        return htmlspecialchars($matches[1], ENT_COMPAT, 'UTF-8');
    }
    
    /**
     * Takes a string of HTML (fragment or document) and returns the content
     */
    function extractBody($html, $return_bool = false) {
        $matches = array();
        $result = preg_match('!<body[^>]*>(.+?)</body>!is', $html, $matches);
        if ($return_bool) return $result;
        if ($result) {
            return $matches[1];
        } else {
            return $html;
        }
    }
    
    /**
     * Currently converts UTF8 into an array of Unicode codepoints. (changing)
     * 
     * We're going to convert this into a multi-purpose UTF-8 well-formedness
     * checker as well as handler for the control characters that are illegal
     * in SGML documents.  But *after* we draw up some unit-tests.  This means
     * that the function, in the end, will not return an array of codepoints
     * but a valid UTF8 string, with non-SGML codepoints excluded.
     * 
     * @note Just for reference, the non-SGML code points are 0 to 31 and
     *       127 to 159, inclusive.
     * 
     * @note The functionality provided by the original function could be
     *       implemented with iconv using 'UTF-8//IGNORE', mbstring, or
     *       even the PCRE modifier 'u', these do not allow us to strip
     *       control characters or disallowed code points, and the latter
     *       does not allow invalid UTF8 characters to be ignored.
     * 
     * @note Decomposing the string into Unicode code points is necessary
     *       because SGML disallows the use of specific code points, not
     *       necessarily bytes.  A naive implementation that simply strtr
     *       disallowed code points as bytes will break other Unicode
     *       characters in which using such bytes is valid.
     * 
     * @note Code adapted from utf8ToUnicode by Henri Sivonen and
     *       hsivonen@iki.fi at <http://iki.fi/hsivonen/php-utf8/> under the
     *       LGPL license.
     */
    function cleanUTF8($str) {
        $mState = 0; // cached expected number of octets after the current octet
                     // until the beginning of the next UTF8 character sequence
        $mUcs4  = 0; // cached Unicode character
        $mBytes = 1; // cached expected number of octets in the current sequence
        
        $out = array();
        
        $len = strlen($str);
        for($i = 0; $i < $len; $i++) {
            $in = ord($str{$i});
            if (0 == $mState) {
                // When mState is zero we expect either a US-ASCII character 
                // or a multi-octet sequence.
                if (0 == (0x80 & ($in))) {
                    // US-ASCII, pass straight through.
                    $out[] = $in;
                    $mBytes = 1;
                } elseif (0xC0 == (0xE0 & ($in))) {
                    // First octet of 2 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 == (0xF0 & ($in))) {
                    // First octet of 3 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 == (0xF8 & ($in))) {
                    // First octet of 4 octet sequence
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 == (0xFC & ($in))) {
                    // First octet of 5 octet sequence.
                    // 
                    // This is illegal because the encoded codepoint must be 
                    // either:
                    // (a) not the shortest form or
                    // (b) outside the Unicode range of 0-0x10FFFF.
                    // Rather than trying to resynchronize, we will carry on 
                    // until the end of the sequence and let the later error
                    // handling code catch it.
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC == (0xFE & ($in))) {
                    // First octet of 6 octet sequence, see comments for 5
                    // octet sequence.
                    $mUcs4 = ($in);
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                    // Current octet is neither in the US-ASCII range nor a 
                    // legal first octet of a multi-octet sequence.
                    return false;
                }
            } else {
                // When mState is non-zero, we expect a continuation of the
                // multi-octet sequence
                if (0x80 == (0xC0 & ($in))) {
                    // Legal continuation.
                    $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;
                    
                    if (0 == --$mState) {
                        // End of the multi-octet sequence. mUcs4 now contains
                        // the final Unicode codepoint to be output
                        
                        // Check for illegal sequences and codepoints.
                        
                        // From Unicode 3.1, non-shortest form is illegal
                        if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                            ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                            ((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
                            (4 < $mBytes) ||
                            // From Unicode 3.2, surrogate characters = illegal
                            (($mUcs4 & 0xFFFFF800) == 0xD800) ||
                            // Codepoints outside the Unicode range are illegal
                            ($mUcs4 > 0x10FFFF)
                        ) {
                            return false;
                        }
                        if (0xFEFF != $mUcs4) {
                            // BOM is legal but we don't want to output it
                            $out[] = $mUcs4;
                        }
                        //initialize UTF8 cache
                        $mState = 0;
                        $mUcs4  = 0;
                        $mBytes = 1;
                    }
                } else {
                    // ((0xC0 & (*in) != 0x80) && (mState != 0))
                     * 
                     * Incomplete multi-octet sequence.
                     */
                    return false;
                }
            }
        }
        return $out;
    }
    
}

?>