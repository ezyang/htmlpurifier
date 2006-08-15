<?php

require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/TokenFactory.php';

/**
 * Parser that uses PHP 5's DOM extension (part of the core).
 * 
 * In PHP 5, the DOM XML extension was revamped into DOM and added to the core.
 * It gives us a forgiving HTML parser, which we use to transform the HTML
 * into a DOM, and then into the tokens.  It is blazingly fast (for large
 * documents, it performs twenty times faster than
 * HTMLPurifier_Lexer_DirectLex,and is the default choice for PHP 5. 
 * 
 * @notice
 * Any empty elements will have empty tokens associated with them, even if
 * this is prohibited by the spec. This is cannot be fixed until the spec
 * comes into play.
 * 
 * @todo Determine DOM's entity parsing behavior, point to local entity files
 *       if necessary.
 * @todo Make div access less fragile, and refrain from preprocessing when
 *       HTML tag and friends are already present.
 */

class HTMLPurifier_Lexer_DOMLex extends HTMLPurifier_Lexer
{
    
    private $factory;
    
    public function __construct() {
        // setup the factory
        $this->factory = new HTMLPurifier_TokenFactory();
    }
    
    public function tokenizeHTML($string, $config = null) {
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        
        if ($config->get('Core', 'AcceptFullDocuments')) {
            $is_full = $this->extractBody($string, true);
        }
        
        $doc = new DOMDocument();
        $doc->encoding = 'UTF-8'; // technically does nothing, but whatever
        
        // replace and escape the CDATA sections, since parsing under HTML
        // mode won't get 'em.
        $string = $this->escapeCDATA($string);
        
        if (!$is_full) {
        // preprocess string, essential for UTF-8
        $string =
            '<html><head>'.
            '<meta http-equiv="Content-Type" content="text/html;'.
                ' charset=utf-8" />'.
            '</head><body>'.$string.'</body></html>';
        }
        
        @$doc->loadHTML($string); // mute all errors, handle it transparently
        
        $tokens = array();
        $this->tokenizeDOM(
            $doc->childNodes->item(1)-> // html
                  getElementsByTagName('body')->item(0) // body
            , $tokens);
        return $tokens;
    }
    
    /**
     * Recursive function that tokenizes a node, putting it into an accumulator.
     * 
     * @param $node     DOMNode to be tokenized.
     * @param $tokens   Array-list of already tokenized tokens.
     * @param $collect  Says whether or start and close are collected, set to
     *                  false at first recursion because it's the implicit DIV
     *                  tag you're dealing with.
     * @returns Tokens of node appended to previously passed tokens.
     */
    protected function tokenizeDOM($node, &$tokens, $collect = false) {
        // recursive goodness!
        
        // intercept non element nodes
        
        if ( !($node instanceof DOMElement) ) {
            if ($node instanceof DOMComment) {
                $tokens[] = $this->factory->createComment($node->data);
            } elseif ($node instanceof DOMText ||
                      $node instanceof DOMCharacterData) {
                $tokens[] = $this->factory->createText($node->data);
            }
            // quite possibly, the object wasn't handled, that's fine
            return;
        }
        
        // We still have to make sure that the element actually IS empty
        if (!$node->hasChildNodes()) {
            if ($collect) {
                $tokens[] = $this->factory->createEmpty(
                    $node->tagName,
                    $this->transformAttrToAssoc($node->attributes)
                );
            }
        } else {
            if ($collect) { // don't wrap on first iteration
                $tokens[] = $this->factory->createStart(
                    $tag_name = $node->tagName, // somehow, it get's dropped
                    $this->transformAttrToAssoc($node->attributes)
                );
            }
            foreach ($node->childNodes as $node) {
                // remember, it's an accumulator. Otherwise, we'd have
                // to use array_merge
                $this->tokenizeDOM($node, $tokens, true);
            }
            if ($collect) {
                $tokens[] = $this->factory->createEnd($tag_name);
            }
        }
        
    }
    
    /**
     * Converts a DOMNamedNodeMap of DOMAttr objects into an assoc array.
     * 
     * @param $attribute_list DOMNamedNodeMap of DOMAttr objects.
     * @returns Associative array of attributes.
     */
    protected function transformAttrToAssoc($attribute_list) {
        $attribute_array = array();
        // undocumented behavior
        foreach ($attribute_list as $key => $attr) {
            $attribute_array[$key] = $attr->value;
        }
        return $attribute_array;
    }
    
}

?>