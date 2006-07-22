<?php

require_once 'XML/HTMLSax3.php'; // PEAR
require_once 'HTMLPurifier/Lexer.php';

// uses the PEAR class XML_HTMLSax3 to parse XML
class HTMLPurifier_Lexer_PEARSax3 extends HTMLPurifier_Lexer
{
    
    var $tokens;
    
    function tokenizeHTML($html) {
        $this->tokens = array();
        $parser=& new XML_HTMLSax3();
        $parser->set_object($this);
        $parser->set_element_handler('openHandler','closeHandler');
        $parser->set_data_handler('dataHandler');
        $parser->set_escape_handler('escapeHandler');
        $parser->set_option('XML_OPTION_ENTITIES_PARSED', 1);
        $parser->parse($html);
        return $this->tokens;
    }
    
    function openHandler(&$parser, $name, $attrs, $closed) {
        if ($closed) {
            $this->tokens[] = new HTMLPurifier_Token_Empty($name, $attrs);
        } else {
            $this->tokens[] = new HTMLPurifier_Token_Start($name, $attrs);
        }
        return true;
    }
    
    function closeHandler(&$parser, $name) {
        // HTMLSax3 seems to always send empty tags an extra close tag
        // check and ignore if you see it:
        // [TESTME] to make sure it doesn't overreach
        if ($this->tokens[count($this->tokens)-1]->type == 'empty') {
            return true;
        }
        $this->tokens[] = new HTMLPurifier_Token_End($name);
        return true;
    }
    
    function dataHandler(&$parser, $data) {
        $this->tokens[] = new HTMLPurifier_Token_Text($data);
        return true;
    }
    
    function escapeHandler(&$parser, $data) {
        if (strpos($data, '-') === 0) {
            $this->tokens[] = new HTMLPurifier_Token_Comment($data);
        }
        return true;
    }
    
}

?>