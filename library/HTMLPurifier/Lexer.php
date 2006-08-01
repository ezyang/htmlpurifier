<?php

require_once 'HTMLPurifier/Token.php';

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
    function tokenizeHTML($string) {
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
     * @param $matches  PCRE matches array, with 0 the entire match, and
     *                  either index 1, 2 or 3 set with a hex value, dec value,
     *                  or string (respectively).
     * @returns Replacement string.
     * @todo Implement string translations
     */
    function nonSpecialEntityCallback($matches) {
        // replaces all but big five
        $entity = $matches[0];
        $is_num = (@$matches[0][1] === '#');
        if ($is_num) {
            $is_hex = (@$entity[2] === 'x');
            $int = $is_hex ? hexdec($matches[1]) : (int) $matches[2];
            if (isset($this->_special_dec2str[$int]))  return $entity;
            return chr($int);
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
    
}

?>