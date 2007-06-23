<?php

require_once 'HTMLPurifier/Injector.php';

/**
 * Injector that auto paragraphs text in the root node based on
 * double-spacing.
 */
class HTMLPurifier_Injector_AutoParagraph extends HTMLPurifier_Injector
{
    
    function handleText(&$token, $config, &$context) {
        $current_nesting =& $context->get('CurrentNesting');
        $text = $token->data;
        // $token is the focus: if processing is needed, it gets
        // turned into an array of tokens that will replace the
        // original token
        if (empty($current_nesting)) {
            // we're in root node, great time to start a paragraph
            // since we're also dealing with a text node
            $token = array(new HTMLPurifier_Token_Start('p'));
            $this->_splitText($text, $token, $config, $context);
        } elseif ($current_nesting[count($current_nesting)-1]->name == 'p') {
            // we're not in root node but we're in a paragraph, so don't 
            // add a paragraph start tag but still perform processing
            $token = array();
            $this->_splitText($text, $token, $config, $context);
        }
    }
    
    function handleStart(&$token, $config, &$context) {
        // check if we're inside a tag already, if so, don't add
        // paragraph tags
        $current_nesting = $context->get('CurrentNesting');
        if (!empty($current_nesting)) return;
        
        // check if the start tag counts as a "block" element
        if (!$this->_isInline($token, $config)) return;
        
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
    function _splitText($data, &$result, $config, &$context) {
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
        if ($this->_removeParagraphEnd($config, $context)) {
            array_pop($result);
        }
        
        
    }
    
    /**
     * Returns boolean whether or not to remove the paragraph end tag
     * that was automatically added. The paragraph end tag should be
     * removed unless the next token is a paragraph or block element.
     * @param $config Instance of HTMLPurifier_Config
     * @param $context Instance of HTMLPurifier_Context
     * @private
     */
    function _removeParagraphEnd($config, &$context) {
        $tokens = $context->get('InputTokens');
        $i = $context->get('InputIndex');
        $remove_paragraph_end = true;
        // Start of the checks one after the current token's index
        for ($i++; isset($tokens[$i]); $i++) {
            if ($tokens[$i]->type == 'start' || $tokens[$i]->type == 'empty') {
                $definition = $config->getHTMLDefinition();
                $remove_paragraph_end = $this->_isInline($tokens[$i], $config);
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
     */
    function _isInline($token, $config) {
        $definition = $config->getHTMLDefinition();
        return !isset($definition->info['p']->auto_close[$token->name]);
    }
    
}

?>