<?php

require_once 'HTMLPurifier/Injector.php';

/**
 * Injector that auto paragraphs text in the root node based on
 * double-spacing.
 */
class HTMLPurifier_Injector_AutoParagraph extends HTMLPurifier_Injector
{
    
    function handleText(&$token, $config, &$context) {
        $dnl = PHP_EOL . PHP_EOL; // double-newline
        $current_nesting =& $context->get('CurrentNesting');
        // paragraphing is on
        if (empty($current_nesting)) {
            // we're in root node, great time to start a paragraph
            // since we're also dealing with a text node
            $result =& $context->get('OutputTokens');
            $result[] = new HTMLPurifier_Token_Start('p');
            $current_nesting[] = new HTMLPurifier_Token_Start('p');
            $this->_splitText($token, $config, $context);
        } else {
            // we're not in root node, so let's see whether or not
            // we're in a paragraph
            
            // losslessly access the parent element
            $parent = array_pop($current_nesting);
            $current_nesting[] = $parent;
            
            if ($parent->name === 'p') {
                $this->_splitText($token, $config, $context);
            }
        }
    }
    
    function handleStart(&$token, $config, &$context) {
        // check if we're inside a tag already, if so, don't add
        // paragraph tags
        $current_nesting = $context->get('CurrentNesting');
        if (!empty($current_nesting)) return;
        
        // check if the start tag counts as a "block" element
        $definition = $config->getHTMLDefinition();
        if (isset($definition->info['p']->auto_close[$token->name])) return;
        
        // append a paragraph tag before the token
        $token = array(new HTMLPurifier_Token_Start('p'), $token);
    }
    
    /**
     * Sub-function for auto-paragraphing that takes a token and splits it 
     * up into paragraphs unconditionally. Requires that a paragraph was
     * already started
     */
    function _splitText(&$token, $config, &$context) {
        $dnl = PHP_EOL . PHP_EOL; // double-newline
        $definition = $config->getHTMLDefinition();
        $current_nesting =& $context->get('CurrentNesting');
        
        $raw_paragraphs = explode($dnl, $token->data);
        
        $token = false; // token has been completely dismantled
        
        // remove empty paragraphs
        $paragraphs = array();
        foreach ($raw_paragraphs as $par) {
            if (trim($par) !== '') $paragraphs[] = $par;
        }
        
        $result =& $context->get('OutputTokens');
        
        if (empty($paragraphs) && count($raw_paragraphs) > 1) {
            $result[] = new HTMLPurifier_Token_End('p');
            array_pop($current_nesting);
            return;
        }
        
        foreach ($paragraphs as $data) {
            $result[] = new HTMLPurifier_Token_Text($data);
            $result[] = new HTMLPurifier_Token_End('p');
            $result[] = new HTMLPurifier_Token_Start('p');
        }
        array_pop($result); // remove trailing start token
        
        // check the outside to determine whether or not end
        // paragraph tag is needed (it's already there)
        $end_paragraph = $this->_needsEndTag(
            $context->get('InputTokens'),
            $context->get('InputIndex'),
            $definition
        );
        
        if ($end_paragraph) {
            // things are good as they stand, remove top-level parent
            // that we deferred
            array_pop($current_nesting);
        } else {
            // remove the ending tag, no nesting modifications necessary
            array_pop($result);
        }
        
    }
    
    /**
     * Determines if up-coming code requires an end-paragraph tag,
     * otherwise, keep the paragraph open (don't make another one)
     * @protected
     */
    function _needsEndTag($tokens, $k, $definition) {
        $end_paragraph = false;
        for ($j = $k + 1; isset($tokens[$j]); $j++) {
            if ($tokens[$j]->type == 'start' || $tokens[$j]->type == 'empty') {
                if ($tokens[$j]->name == 'p') {
                    $end_paragraph = true;
                } else {
                    $end_paragraph = isset($definition->info['p']->auto_close[$tokens[$j]->name]);
                }
                break;
            } elseif ($tokens[$j]->type == 'text') {
                if (!$tokens[$j]->is_whitespace) {
                    $end_paragraph = false;
                    break;
                }
            } elseif ($tokens[$j]->type == 'end') {
                // nonsensical case
                $end_paragraph = false;
                break;
            }
        }
        return $end_paragraph;
    }
    
}

?>