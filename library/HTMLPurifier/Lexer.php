<?php

require_once 'HTMLPurifier/Token.php';
require_once 'HTMLPurifier/Encoder.php';

HTMLPurifier_ConfigDef::define(
    'Core', 'AcceptFullDocuments', true, 'bool',
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
    
    function HTMLPurifier_Lexer() {
        $this->_encoder = new HTMLPurifier_Encoder();
    }
    
    var $_encoder;
    
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
    function extractBody($html) {
        $matches = array();
        $result = preg_match('!<body[^>]*>(.+?)</body>!is', $html, $matches);
        if ($result) {
            return $matches[1];
        } else {
            return $html;
        }
    }
    
}

?>