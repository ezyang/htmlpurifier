<?php

require_once 'HTMLPurifier/Injector.php';

/**
 * Injector that auto paragraphs text in the root node based on
 * double-spacing.
 */
class HTMLPurifier_Injector_AutoParagraph extends HTMLPurifier_Injector
{
    
    function handleText(&$token) {
        $text = $token->data;
        // $token is the focus: if processing is needed, it gets
        // turned into an array of tokens that will replace the
        // original token
        if (empty($this->currentNesting)) {
            if (!$this->allowsElement('p')) return;
            // we're in root node, and the root node allows paragraphs
            // start a paragraph since we just hit some text
            $token = array(new HTMLPurifier_Token_Start('p'));
            $this->_splitText($text, $token);
        } elseif ($this->currentNesting[count($this->currentNesting)-1]->name == 'p') {
            // we're not in root node but we're in a paragraph, so don't 
            // add a paragraph start tag but still perform processing
            $token = array();
            $this->_splitText($text, $token);
        }
    }
    
    function handleStart(&$token) {
        // check if we're inside a tag already, if so, don't add
        // paragraph tags
        if (!empty($this->currentNesting)) return;
        
        // check if the start tag counts as a "block" element
        if (!$this->_isInline($token)) return;
        
        // append a paragraph tag before the token
        $token = array(new HTMLPurifier_Token_Start('p'), $token);
    }
    
    /**
     * Splits up a text in paragraph tokens and appends them
     * to the result stream that will replace the original
     * @param $data String text data that will be processed
     *    into paragraphs
     * @param $result Reference to array of tokens that the
     *    tags will be appended onto
     * @param $config Instance of HTMLPurifier_Config
     * @param $context Instance of HTMLPurifier_Context
     * @private
     */
    function _splitText($data, &$result) {
        $raw_paragraphs = explode(PHP_EOL . PHP_EOL, $data);
        
        // remove empty paragraphs
        $paragraphs = array();
        foreach ($raw_paragraphs as $par) {
            if (trim($par) !== '') $paragraphs[] = $par;
        }
        
        // check if there are no "real" paragraphs to be processed
        if (empty($paragraphs) && count($raw_paragraphs) > 1) {
            $result[] = new HTMLPurifier_Token_End('p');
            return;
        }
        
        // append the paragraphs onto the result
        foreach ($paragraphs as $par) {
            $result[] = new HTMLPurifier_Token_Text($par);
            $result[] = new HTMLPurifier_Token_End('p');
            $result[] = new HTMLPurifier_Token_Start('p');
        }
        array_pop($result); // remove trailing start token
        
        // check the outside to determine whether or not the
        // end paragraph tag should be removed
        if ($this->_removeParagraphEnd()) {
            array_pop($result);
        }
        
        
    }
    
    /**
     * Returns boolean whether or not to remove the paragraph end tag
     * that was automatically added. The paragraph end tag should be
     * removed unless the next token is a paragraph or block element.
     * @private
     */
    function _removeParagraphEnd() {
        $tokens =& $this->inputTokens;
        $remove_paragraph_end = true;
        // Start of the checks one after the current token's index
        for ($i = $this->inputIndex + 1; isset($tokens[$i]); $i++) {
            if ($tokens[$i]->type == 'start' || $tokens[$i]->type == 'empty') {
                $remove_paragraph_end = $this->_isInline($tokens[$i]);
                break;
            }
            // check if we can abort early (whitespace means we carry-on!)
            if ($tokens[$i]->type == 'text' && !$tokens[$i]->is_whitespace) break;
            if ($tokens[$i]->type == 'end') break; // nonsensical
        }
        return $remove_paragraph_end;
    }
    
    /**
     * Returns true if passed token is inline (and, ergo, allowed in
     * paragraph tags)
     * @private
     */
    function _isInline($token) {
        return isset($this->htmlDefinition->info['p']->child->elements[$token->name]);
    }
    
}

?>