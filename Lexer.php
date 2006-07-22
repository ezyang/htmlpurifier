<?php

/**
 * Forgivingly lexes SGML style documents: HTML, XML, XHTML, etc.
 */

require_once 'HTMLPurifier/Token.php';

class HTMLPurifier_Lexer
{
    
    function tokenizeHTML($string) {
        trigger_error('Call to abstract class', E_USER_ERROR);
    }
    
    // we don't really care if it's a reference or a copy
    
    function create($prototype = null) {
        static $lexer = null;
        if ($prototype) {
            $lexer = $prototype;
        }
        if (empty($lexer)) {
            require_once 'HTMLPurifier/Lexer/DirectLex.php';
            $lexer = new HTMLPurifier_Lexer_DirectLex();
        }
        return $lexer;
    }
    
}

?>