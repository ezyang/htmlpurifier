<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/HTMLDefinition.php';
require_once 'HTMLPurifier/Generator.php';

HTMLPurifier_ConfigSchema::define(
    'Core', 'AutoParagraph', false, 'bool', '
<p>
  This directive will cause HTML Purifier to automatically paragraph text
  in the document fragment root based on two newlines and block tags.
  This directive has been available since 2.0.1.
</p>
'
);

/**
 * Takes tokens makes them well-formed (balance end tags, etc.)
 */
class HTMLPurifier_Strategy_MakeWellFormed extends HTMLPurifier_Strategy
{
    
    function execute($tokens, $config, &$context) {
        
        $definition = $config->getHTMLDefinition();
        $generator = new HTMLPurifier_Generator();
        
        $current_nesting = array();
        $context->register('CurrentNesting', $current_nesting);
        
        $tokens_index = null;
        $context->register('InputIndex', $tokens_index);
        $context->register('InputTokens', $tokens);
        
        $result = array();
        $context->register('OutputTokens', $result);
        
        $escape_invalid_tags = $config->get('Core', 'EscapeInvalidTags');
        $auto_paragraph      = $config->get('Core', 'AutoParagraph');
        
        $auto_paragraph_skip = 0;
        $auto_paragraph_disabled = false;
        $context->register('AutoParagraphSkip', $auto_paragraph_skip);
        
        for ($tokens_index = 0; isset($tokens[$tokens_index]); $tokens_index++) {
            
            // if all goes well, this token will be passed through unharmed
            $token = $tokens[$tokens_index];
            
            // this will be more complicated
            if ($auto_paragraph_skip > 0) {
                $auto_paragraph_skip--;
                $auto_paragraph_disabled = true;
            } else {
                $auto_paragraph_disabled = false;
            }
            
            // quick-check: if it's not a tag, no need to process
            if (empty( $token->is_tag )) {
                
                if ($token->type === 'text') {
                     if ($auto_paragraph && !$auto_paragraph_disabled) {
                         $this->autoParagraphText($token, $config, $context);
                     }
                }
                
                $this->processToken($token, $config, $context);
                continue;
            }
            
            $info = $definition->info[$token->name]->child;
            
            // test if it claims to be a start tag but is empty
            if ($info->type == 'empty' && $token->type == 'start') {
                $result[] = new HTMLPurifier_Token_Empty($token->name, $token->attr);
                continue;
            }
            
            // test if it claims to be empty but really is a start tag
            if ($info->type != 'empty' && $token->type == 'empty' ) {
                $result[] = new HTMLPurifier_Token_Start($token->name, $token->attr);
                $result[] = new HTMLPurifier_Token_End($token->name);
                continue;
            }
            
            // automatically insert empty tags
            if ($token->type == 'empty') {
                $result[] = $token;
                continue;
            }
            
            // start tags have precedence, so they get passed through...
            if ($token->type == 'start') {
                
                // ...unless they also have to close their parent
                if (!empty($current_nesting)) {
                    
                    $parent = array_pop($current_nesting);
                    $parent_info = $definition->info[$parent->name];
                    
                    // this can be replaced with a more general algorithm:
                    // if the token is not allowed by the parent, auto-close
                    // the parent
                    if (isset($parent_info->auto_close[$token->name])) {
                        // close th e parent, then append the token
                        $result[] = new HTMLPurifier_Token_End($parent->name);
                        $result[] = $token;
                        $current_nesting[] = $token;
                        continue;
                    }
                    
                    $current_nesting[] = $parent; // undo the pop
                }
                
                if ($auto_paragraph && !$auto_paragraph_disabled) {
                    $this->autoParagraphStart($token, $config, $context);
                }
                
                $this->processToken($token, $config, $context);
                continue;
            }
            
            // sanity check: we should be dealing with a closing tag
            if ($token->type != 'end') continue;
            
            // make sure that we have something open
            if (empty($current_nesting)) {
                if ($escape_invalid_tags) {
                    $result[] = new HTMLPurifier_Token_Text(
                        $generator->generateFromToken($token, $config, $context)
                    );
                }
                continue;
            }
            
            // first, check for the simplest case: everything closes neatly
            $current_parent = array_pop($current_nesting);
            if ($current_parent->name == $token->name) {
                $result[] = $token;
                continue;
            }
            
            // okay, so we're trying to close the wrong tag
            
            // undo the pop previous pop
            $current_nesting[] = $current_parent;
            
            // scroll back the entire nest, trying to find our tag.
            // (feature could be to specify how far you'd like to go)
            $size = count($current_nesting);
            // -2 because -1 is the last element, but we already checked that
            $skipped_tags = false;
            for ($i = $size - 2; $i >= 0; $i--) {
                if ($current_nesting[$i]->name == $token->name) {
                    // current nesting is modified
                    $skipped_tags = array_splice($current_nesting, $i);
                    break;
                }
            }
            
            // we still didn't find the tag, so remove
            if ($skipped_tags === false) {
                if ($escape_invalid_tags) {
                    $result[] = new HTMLPurifier_Token_Text(
                        $generator->generateFromToken($token, $config, $context)
                    );
                }
                continue;
            }
            
            // okay, we found it, close all the skipped tags
            // note that skipped tags contains the element we need closed
            $size = count($skipped_tags);
            for ($i = $size - 1; $i >= 0; $i--) {
                $result[] = new HTMLPurifier_Token_End($skipped_tags[$i]->name);
            }
            
        }
        
        // we're at the end now, fix all still unclosed tags
        // not using processToken() because at this point we don't
        // care about current nesting
        if (!empty($current_nesting)) {
            $size = count($current_nesting);
            for ($i = $size - 1; $i >= 0; $i--) {
                $result[] =
                    new HTMLPurifier_Token_End($current_nesting[$i]->name);
            }
        }
        
        $context->destroy('CurrentNesting');
        $context->destroy('InputTokens');
        $context->destroy('InputIndex');
        $context->destroy('OutputTokens');
        
        return $result;
    }
    
    function processToken($token, $config, &$context) {
        if (is_array($token)) {
            // the original token was overloaded by a formatter, time
            // to some fancy acrobatics
            
            $tokens              =& $context->get('InputTokens');
            $tokens_index        =& $context->get('InputIndex');
            // $tokens_index is decremented so that the entire set gets
            // re-processed
            array_splice($tokens, $tokens_index--, 1, $token);
            
            // this will be a bit more complicated when we add more formatters
            // we need to prevent the same formatter from running twice on it
            $auto_paragraph_skip =& $context->get('AutoParagraphSkip');
            $auto_paragraph_skip = count($token);
            
        } elseif ($token) {
            // regular case
            $result =& $context->get('OutputTokens');
            $current_nesting =& $context->get('CurrentNesting');
            $result[] = $token;
            if ($token->type == 'start') {
                $current_nesting[] = $token;
            } elseif ($token->type == 'end') {
                // theoretical: this isn't used because performing
                // the calculations otherwise is more efficient
                array_pop($current_nesting);
            }
        }
    }
    
    /**
     * Sub-function call for auto-paragraphing for any old text node.
     * This will eventually
     * be factored out into a generic Formatter class
     * @note This function does not care at all about ending paragraph
     *       tags: the rest of MakeWellFormed handles that!
     */
    function autoParagraphText(&$token, $config, &$context) {
        $dnl = PHP_EOL . PHP_EOL; // double-newline
        $current_nesting =& $context->get('CurrentNesting');
        // paragraphing is on
        if (empty($current_nesting)) {
            // we're in root node, great time to start a paragraph
            // since we're also dealing with a text node
            $result =& $context->get('OutputTokens');
            $result[] = new HTMLPurifier_Token_Start('p');
            $current_nesting[] = new HTMLPurifier_Token_Start('p');
            $this->autoParagraphSplitText($token, $config, $context);
        } else {
            // we're not in root node, so let's see whether or not
            // we're in a paragraph
            
            // losslessly access the parent element
            $parent = array_pop($current_nesting);
            $current_nesting[] = $parent;
            
            if ($parent->name === 'p') {
                $this->autoParagraphSplitText($token, $config, $context);
            }
        }
    }
    
    /**
     * Sub-function for auto-paragraphing that takes a token and splits it 
     * up into paragraphs unconditionally. Requires that a paragraph was
     * already started
     */
    function autoParagraphSplitText(&$token, $config, &$context) {
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
            $this->processToken(new HTMLPurifier_Token_End('p'), $config, $context);
            return;
        }
        
        // this could be rewritten to use processToken, but it would
        // be slightly less efficient
        
        foreach ($paragraphs as $data) {
            $result[] = new HTMLPurifier_Token_Text($data);
            $result[] = new HTMLPurifier_Token_End('p');
            $result[] = new HTMLPurifier_Token_Start('p');
        }
        array_pop($result); // remove trailing start token
        
        // check the outside to determine whether or not end
        // paragraph tag is needed (it's already there)
        $end_paragraph = $this->autoParagraphEndParagraph(
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
    function autoParagraphEndParagraph($tokens, $k, $definition) {
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
    
    /**
     * Sub-function for auto-paragraphing that processes element starts
     */
    function autoParagraphStart(&$token, $config, &$context) {
        $current_nesting =& $context->get('CurrentNesting');
        if (!empty($current_nesting)) return;
        $definition = $config->getHTMLDefinition();
        // to be replaced with new auto-auto_close algorithm
        if (isset($definition->info['p']->auto_close[$token->name])) return;
        $result =& $context->get('OutputTokens');
        $this->processToken(new HTMLPurifier_Token_Start('p'), $config, $context);
    }
    
}

?>