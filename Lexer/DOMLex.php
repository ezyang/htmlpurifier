<?php

require_once 'HTMLPurifier/Lexer.php';

// PHP5 only!

class HTMLPurifier_Lexer_DOMLex extends HTMLPurifier_Lexer
{
    
    public function tokenizeHTML($string) {
        $doc = new DOMDocument();
        // preprocess string
        $string = '<html><body><div>'.$string.'</div></body></html>';
        @$doc->loadHTML($string); // mute all errors, handle it transparently
        return $this->tokenizeDOM(
            $doc->childNodes->item(1)-> // html
                  childNodes->item(0)-> // body
                  childNodes->item(0)   // div
            );
    }
    
    protected function tokenizeDOM($node, $tokens = array(), $collect = false) {
        // recursive goodness!
        
        // intercept non element nodes
        
        if ( !($node instanceof DOMElement) ) {
            if ($node instanceof DOMComment) {
                $tokens[] = new HTMLPurifier_Token_Comment($node->data);
            } elseif ($node instanceof DOMText) {
                $tokens[] = new HTMLPurifier_Token_Text($node->data);
            }
            // quite possibly, the object wasn't handled, that's fine
            return $tokens;
        }
        
        // We still have to make sure that the element actually IS empty
        if (!$node->hasChildNodes()) {
            if ($collect) {
                $tokens[] = new HTMLPurifier_Token_Empty(
                    $node->tagName,
                    $this->transformAttrToAssoc($node->attributes)
                );
            }
        } else {
            if ($collect) { // don't wrap on first iteration
                $tokens[] = new HTMLPurifier_Token_Start(
                    $tag_name = $node->tagName, // somehow, it get's dropped
                    $this->transformAttrToAssoc($node->attributes)
                );
            }
            foreach ($node->childNodes as $node) {
                // remember, it's an accumulator. Otherwise, we'd have
                // to use array_merge
                $tokens = $this->tokenizeDOM($node, $tokens, true);
            }
            if ($collect) {
                $tokens[] = new HTMLPurifier_Token_End($tag_name);
            }
        }
        
        return $tokens;
        
    }
    
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