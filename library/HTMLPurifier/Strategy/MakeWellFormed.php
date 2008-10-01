<?php

/**
 * Takes tokens makes them well-formed (balance end tags, etc.)
 */
class HTMLPurifier_Strategy_MakeWellFormed extends HTMLPurifier_Strategy
{
    
    /**
     * Locally shared variable references
     */
    protected $inputTokens, $inputIndex, $outputTokens, $currentNesting,
        $currentInjector, $injectors;
    
    public function execute($tokens, $config, $context) {
        
        $definition = $config->getHTMLDefinition();
        
        // local variables
        $generator = new HTMLPurifier_Generator($config, $context);
        $escape_invalid_tags = $config->get('Core', 'EscapeInvalidTags');
        $e = $context->get('ErrorCollector', true);
        
        // member variables
        $this->currentNesting = array();
        $this->inputIndex     = false;
        $this->inputTokens    =& $tokens;
        $this->config         = $config;
        $this->context        = $context;
        
        // context variables
        $context->register('CurrentNesting', $this->currentNesting);
        $context->register('InputIndex',     $this->inputIndex);
        $context->register('InputTokens',    $tokens);
        
        // -- begin INJECTOR --
        
        $this->injectors = array();
        
        $injectors = $config->getBatch('AutoFormat');
        $def_injectors = $definition->info_injector;
        $custom_injectors = $injectors['Custom'];
        unset($injectors['Custom']); // special case
        foreach ($injectors as $injector => $b) {
            $injector = "HTMLPurifier_Injector_$injector";
            if (!$b) continue;
            $this->injectors[] = new $injector;
        }
        foreach ($def_injectors as $injector) {
            // assumed to be objects
            $this->injectors[] = $injector;
        }
        foreach ($custom_injectors as $injector) {
            if (is_string($injector)) {
                $injector = "HTMLPurifier_Injector_$injector";
                $injector = new $injector;
            }
            $this->injectors[] = $injector;
        }
        
        // array index of the injector that resulted in an array
        // substitution. This enables processTokens() to know which
        // injectors are affected by the added tokens and which are
        // not (namely, the ones after the current injector are not
        // affected)
        $this->currentInjector = false;
        
        // give the injectors references to the definition and context
        // variables for performance reasons
        foreach ($this->injectors as $i => $injector) {
            $error = $injector->prepare($config, $context);
            if (!$error) continue;
            array_splice($this->injectors, $i, 1); // rm the injector
            trigger_error("Cannot enable {$injector->name} injector because $error is not allowed", E_USER_WARNING);
        }
        
        // -- end INJECTOR --
        
        $token = false;
        $context->register('CurrentToken', $token);
        
        $reprocess = false;
        $i = false; // injector index
        
        // isset is in loop because $tokens size changes during loop exec
        for (
            $this->inputIndex = 0;
            $this->inputIndex == 0 || isset($tokens[$this->inputIndex - 1]);
            // only increment if we don't need to reprocess
            $reprocess ? $reprocess = false : $this->inputIndex++
        ) {
            
            // check for a rewind
            if (is_int($i) && $i >= 0) {
                $rewind_to = $this->injectors[$i]->getRewind();
                if (is_int($rewind_to) && $rewind_to < $this->inputIndex) {
                    if ($rewind_to < 0) $rewind_to = 0;
                    while ($this->inputIndex > $rewind_to) {
                        $this->inputIndex--;
                        $prev = $this->inputTokens[$this->inputIndex];
                        // indicate that other injectors should not process this token,
                        // but we need to reprocess it
                        unset($prev->skip[$i]);
                        $prev->rewind = $i;
                        if ($prev instanceof HTMLPurifier_Token_Start) array_pop($this->currentNesting);
                        elseif ($prev instanceof HTMLPurifier_Token_End) $this->currentNesting[] = $prev->start;
                    }
                }
                $i = false;
            }
            
            // handle case of document end
            if (!isset($tokens[$this->inputIndex])) {
                // We're at the end now, fix all still unclosed tags.
                // This would logically go at the end of the loop, but because
                // of all of the callbacks we need to be able to run the loop
                // again.
                
                // kill processing if stack is empty
                if (empty($this->currentNesting)) {
                    break; 
                }
                
                // peek
                $top_nesting = array_pop($this->currentNesting);
                $this->currentNesting[] = $top_nesting;
                
                // send error
                if ($e && !isset($top_nesting->armor['MakeWellFormed_TagClosedError'])) {
                    $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by document end', $top_nesting);
                }
                
                // append, don't splice, since this is the end
                $tokens[] = new HTMLPurifier_Token_End($top_nesting->name);
                
                // punt!
                $reprocess = true;
                continue;
            }
            
            // if all goes well, this token will be passed through unharmed
            $token = $tokens[$this->inputIndex];
            
            //echo '<hr>';
            //printTokens($this->inputTokens, $this->inputIndex);
            //var_dump($this->currentNesting);
            
            // quick-check: if it's not a tag, no need to process
            if (empty($token->is_tag)) {
                if ($token instanceof HTMLPurifier_Token_Text) {
                    foreach ($this->injectors as $i => $injector) {
                        if (isset($token->skip[$i])) continue;
                        if ($token->rewind !== null && $token->rewind !== $i) continue;
                        $injector->handleText($token);
                        $this->processToken($token, $i);
                        $reprocess = true;
                        break;
                    }
                }
                continue;
            }
            
            if (isset($definition->info[$token->name])) {
                $type = $definition->info[$token->name]->child->type;
            } else {
                $type = false; // Type is unknown, treat accordingly
            }
            
            // quick tag checks: anything that's *not* an end tag
            $ok = false;
            if ($type === 'empty' && $token instanceof HTMLPurifier_Token_Start) {
                // test if it claims to be a start tag but is empty
                $token = new HTMLPurifier_Token_Empty($token->name, $token->attr);
                $ok = true;
            } elseif ($type && $type !== 'empty' && $token instanceof HTMLPurifier_Token_Empty) {
                // claims to be empty but really is a start tag
                $this->swap(new HTMLPurifier_Token_End($token->name));
                $this->insertBefore(new HTMLPurifier_Token_Start($token->name, $token->attr));
                // punt
                $reprocess = true;
                continue;
            } elseif ($token instanceof HTMLPurifier_Token_Empty) {
                // real empty token
                $ok = true;
            } elseif ($token instanceof HTMLPurifier_Token_Start) {
                // start tag
                
                // ...unless they also have to close their parent
                if (!empty($this->currentNesting)) {
                    
                    $parent = array_pop($this->currentNesting);
                    if (isset($definition->info[$parent->name])) {
                        $elements = $definition->info[$parent->name]->child->getNonAutoCloseElements($config);
                        $autoclose = !isset($elements[$token->name]);
                    } else {
                        $autoclose = false;
                    }
                    
                    if ($autoclose) {
                        if ($e) $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag auto closed', $parent);
                        // insert parent end tag before this tag; 
                        // end tag isn't processed, but this tag is processed again
                        $new_token = new HTMLPurifier_Token_End($parent->name);
                        $new_token->start = $parent;
                        $this->insertBefore($new_token);
                        continue;
                    }
                    
                    $this->currentNesting[] = $parent; // undo the pop
                }
                $ok = true;
            }
            
            // injector handler code; duplicated for performance reasons
            if ($ok) {
                foreach ($this->injectors as $i => $injector) {
                    if (isset($token->skip[$i])) continue;
                    if ($token->rewind !== null && $token->rewind !== $i) continue;
                    $injector->handleElement($token);
                    $this->processToken($token, $i);
                    $reprocess = true;
                    break;
                }
                if (!$reprocess) {
                    // ah, nothing interesting happened; do normal processing
                    $this->swap($token);
                    if ($token instanceof HTMLPurifier_Token_Start) {
                        $this->currentNesting[] = $token;
                    } elseif ($token instanceof HTMLPurifier_Token_End) {
                        throw new HTMLPurifier_Exception('Improper handling of end tag in start code; possible error in MakeWellFormed');
                    }
                }
                continue;
            }
            
            // sanity check: we should be dealing with a closing tag
            if (!$token instanceof HTMLPurifier_Token_End) {
                $this->remove();
                continue;
            }
            
            // make sure that we have something open
            if (empty($this->currentNesting)) {
                if ($escape_invalid_tags) {
                    if ($e) $e->send(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag to text');
                    $this->swap(new HTMLPurifier_Token_Text(
                        $generator->generateFromToken($token)
                    ));
                } else {
                    $this->remove();
                    if ($e) $e->send(E_WARNING, 'Strategy_MakeWellFormed: Unnecessary end tag removed');
                }
                continue;
            }
            foreach ($this->injectors as $i => $injector) {
                if (isset($token->skip[$i])) continue;
                if ($token->rewind !== null && $token->rewind !== $i) continue;
                $injector->handleEnd($token);
                $this->processToken($token, $i);
                $reprocess = true;
                break;
            }
            if ($reprocess) continue;
            
            // first, check for the simplest case: everything closes neatly
            $current_parent = array_pop($this->currentNesting);
            if ($current_parent->name == $token->name) {
                $token->start = $current_parent;
                continue;
            }
            
            // okay, so we're trying to close the wrong tag
            
            // undo the pop previous pop
            $this->currentNesting[] = $current_parent;
            
            // scroll back the entire nest, trying to find our tag.
            // (feature could be to specify how far you'd like to go)
            $size = count($this->currentNesting);
            // -2 because -1 is the last element, but we already checked that
            $skipped_tags = false;
            for ($i = $size - 2; $i >= 0; $i--) {
                if ($this->currentNesting[$i]->name == $token->name) {
                    // current nesting is modified
                    $skipped_tags = array_splice($this->currentNesting, $i);
                    break;
                }
            }
            
            // we still didn't find the tag, so remove
            if ($skipped_tags === false) {
                if ($escape_invalid_tags) {
                    $this->swap(new HTMLPurifier_Token_Text(
                        $generator->generateFromToken($token)
                    ));
                    if ($e) $e->send(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag to text');
                } else {
                    $this->remove();
                    if ($e) $e->send(E_WARNING, 'Strategy_MakeWellFormed: Stray end tag removed');
                }
                continue;
            }
            
            // okay, we found it, close all the skipped tags
            // note that skipped tags contains the element we need closed
            $this->remove();
            for ($i = count($skipped_tags) - 1; $i >= 0; $i--) {
                if ($i && $e && !isset($skipped_tags[$i]->armor['MakeWellFormed_TagClosedError'])) {
                    $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by element end', $skipped_tags[$i]);
                }
                $new_token = new HTMLPurifier_Token_End($skipped_tags[$i]->name);
                $new_token->start = $skipped_tags[$i];
                $this->insertAfter($new_token);
            }
        }
        
        $context->destroy('CurrentNesting');
        $context->destroy('InputTokens');
        $context->destroy('InputIndex');
        $context->destroy('CurrentToken');
        
        unset($this->outputTokens, $this->injectors, $this->currentInjector,
          $this->currentNesting, $this->inputTokens, $this->inputIndex);
        return $tokens;
    }
    
    /**
     * Processes arbitrary token values for complicated substitution patterns.
     * In general:
     * 
     * If $token is an array, it is a list of tokens to substitute for the
     * current token. These tokens then get individually processed.
     * 
     * If $token is a regular token, it is swapped with the current token,
     * and the stack is updated.
     * 
     * If $token is false, the current token is deleted.
     */
    protected function processToken($token, $injector = -1) {
        
        // normalize forms of token
        if (is_object($token)) $token = array(1, $token);
        if (is_int($token))    $token = array($token);
        if ($token === false)  $token = array(1);
        if (!is_array($token)) throw new HTMLPurifier_Exception('Invalid token type from injector');
        if (!is_int($token[0])) array_unshift($token, 1);
        if ($token[0] === 0) throw new HTMLPurifier_Exception('Deleting zero tokens is not valid');
        
        // $token is now an array with the following form:
        // array(number nodes to delete, new node 1, new node 2, ...)
        
        $delete = array_shift($token);
        $old = array_splice($this->inputTokens, $this->inputIndex, $delete, $token);
        
        if ($injector > -1) {
            // determine appropriate skips
            $oldskip = isset($old[0]) ? $old[0]->skip : array();
            foreach ($token as $object) {
                $object->skip = $oldskip;
                $object->skip[$injector] = true;
            }
        }
        
    }
    
    /**
     * Inserts a token before the current token. Cursor now points to this token
     */
    protected function insertBefore($token) {
        array_splice($this->inputTokens, $this->inputIndex, 0, array($token));
    }

    /**
     * Inserts a token after the current token. Cursor now points to this token
     */
    protected function insertAfter($token) {
        array_splice($this->inputTokens, ++$this->inputIndex, 0, array($token));
    }

    /**
     * Removes current token. Cursor now points to previous token.
     */
    protected function remove() {
        array_splice($this->inputTokens, $this->inputIndex--, 1);
    }
    
    /**
     * Swap current token with new token. Cursor points to new token (no change
     */
    protected function swap($token) {
        array_splice($this->inputTokens, $this->inputIndex, 1, array($token));
    }
    
}

