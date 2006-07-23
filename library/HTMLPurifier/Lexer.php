<?php

require_once 'HTMLPurifier/Token.php';

/**
 * Forgivingly lexes HTML (SGML-style) markup into tokens.
 * 
 * The lexer parses a string of SGML-style markup and converts them into
 * corresponding tokens.  It doesn't check for well-formedness, although its
 * internal mechanism may make this automatic (such as the case of
 * HTMLPurifier_Lexer_DOMLex).  There are several implementations to choose
 * from.
 * 
 * The lexer is HTML-oriented: it might work with XML, but it's not
 * recommended, as we adhere to a subset of the specification for optimization
 * reasons.
 * 
 * This class cannot be directly instantiated, but you may use create() to
 * retrieve a default copy of the lexer.
 * 
 * @note
 * We use tokens rather than create a DOM representation because DOM would:
 * 
 * @note
 *  -# Require more processing power to create,
 *  -# Require recursion to iterate,
 *  -# Must be compatible with PHP 5's DOM (otherwise duplication),
 *  -# Has the entire document structure (html and body not needed), and
 *  -# Has unknown readability improvement.
 * 
 * @note
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
    
}

?>