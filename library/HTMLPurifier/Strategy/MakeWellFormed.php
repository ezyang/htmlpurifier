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
        
        // warning: most foreach loops follow the convention $i => $injector.
        // Don't define these as loop-wide variables, please!
        
        // -- end INJECTOR --
        
        $token = false;
        $context->register('CurrentToken', $token);
        
        // isset is in loop because $tokens size changes during loop exec
        for ($this->inputIndex = 0; isset($tokens[$this->inputIndex]); $this->inputIndex++) {
            
            // if all goes well, this token will be passed through unharmed
            $token = $tokens[$this->inputIndex];
            
            //echo '<hr>';
            //printTokens($tokens, $this->inputIndex);
            //var_dump($this->currentNesting);
            
            foreach ($this->injectors as $injector) {
                if ($injector->skip > 0) $injector->skip--;
            }
            
            // quick-check: if it's not a tag, no need to process
            if (empty( $token->is_tag )) {
                if ($token instanceof HTMLPurifier_Token_Text) {
                     // injector handler code; duplicated for performance reasons
                     foreach ($this->injectors as $i => $injector) {
                         if (!$injector->skip) $injector->handleText($token);
                         if (is_array($token) || is_int($token)) {
                             $this->currentInjector = $i;
                             break;
                         }
                     }
                }
                $this->processToken($token, $config, $context);
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
                $token = array(
                    new HTMLPurifier_Token_Start($token->name, $token->attr),
                    new HTMLPurifier_Token_End($token->name)
                );
                $ok = true;
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
                    if (!$injector->skip) $injector->handleElement($token);
                    if (is_array($token) || is_int($token)) {
                        $this->currentInjector = $i;
                        break;
                    }
                }
                $this->processToken($token, $config, $context);
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
            
            // first, check for the simplest case: everything closes neatly
            $current_parent = array_pop($this->currentNesting);
            if ($current_parent->name == $token->name) {
                $token->start = $current_parent;
                foreach ($this->injectors as $i => $injector) {
                    $injector->notifyEnd($token);
                }
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
                // please don't redefine $i!
                if ($i && $e && !isset($skipped_tags[$i]->armor['MakeWellFormed_TagClosedError'])) {
                    $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by element end', $skipped_tags[$i]);
                }
                $new_token = new HTMLPurifier_Token_End($skipped_tags[$i]->name);
                $new_token->start = $skipped_tags[$i];
                $this->insertAfter($new_token);
                //printTokens($tokens, $this->inputIndex);
                //var_dump($this->currentNesting);
                foreach ($this->injectors as $injector) {
                    $injector->notifyEnd($new_token);
                }
            }
        }
        
        $context->destroy('CurrentNesting');
        $context->destroy('InputTokens');
        $context->destroy('InputIndex');
        $context->destroy('CurrentToken');
        
        // we're at the end now, fix all still unclosed tags (this is
        // duplicated from the end of the loop with some slight modifications)
        // not using $skipped_tags since it would invariably be all of them
        if (!empty($this->currentNesting)) {
            for ($i = count($this->currentNesting) - 1; $i >= 0; $i--) {
                // please don't redefine $i!
                if ($e && !isset($this->currentNesting[$i]->armor['MakeWellFormed_TagClosedError'])) {
                    $e->send(E_NOTICE, 'Strategy_MakeWellFormed: Tag closed by document end', $this->currentNesting[$i]);
                }
                // instead of splice, since we know this is the end
                $tokens[] = $new_token = new HTMLPurifier_Token_End($this->currentNesting[$i]->name);
                $new_token->start = $this->currentNesting[$i];
                foreach ($this->injectors as $injector) {
                    $injector->notifyEnd($new_token);
                }
            }
        }
        
        unset($this->outputTokens, $this->injectors, $this->currentInjector,
          $this->currentNesting, $this->inputTokens, $this->inputIndex);
        return $tokens;
    }
    
    /**
     * Inserts a token before the current token. Cursor now points to this token.
     */
    protected function insertBefore($token) {
        array_splice($this->inputTokens, $this->inputIndex, 0, array($token));
    }
    
    /**
     * Inserts a token after the current token. Cursor now points to this token.
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
     * Swap current token with new token. Cursor points to new token (no change).
     */
    protected function swap($token) {
        array_splice($this->inputTokens, $this->inputIndex, 1, array($token));
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
    protected function processToken($token, $config, $context) {
        if (is_array($token) || is_int($token)) {
            // the original token was overloaded by an injector, time
            // to some fancy acrobatics
            if (is_array($token)) {
                array_splice($this->inputTokens, $this->inputIndex, 1, $token);
            } else {
                array_splice($this->inputTokens, $this->inputIndex, $token, array());
            }
            if ($this->injectors) {
                $rewind = $this->injectors[$this->currentInjector]->getRewind();
                if ($rewind < 0) $rewind = 0;
                if ($rewind !== false) {
                    $offset = $this->inputIndex - $rewind;
                    if ($this->injectors) {
                        foreach ($this->injectors as $i => $injector) {
                            if ($i == $this->currentInjector) {
                                $injector->skip = 0;
                            } else {
                                $injector->skip += $offset;
                            }
                        }
                    }
                    for ($this->inputIndex--; $this->inputIndex >= $rewind; $this->inputIndex--) {
                        $prev = $this->inputTokens[$this->inputIndex];
                        if ($prev instanceof HTMLPurifier_Token_Start) array_pop($this->currentNesting);
                        elseif ($prev instanceof HTMLPurifier_Token_End) $this->currentNesting[] = $prev->start;
                    }
                    $this->inputIndex++;
                } else {
                    // adjust the injector skips based on the array substitution
                    $offset = is_array($token) ? count($token) : 0;
                    for ($i = 0; $i <= $this->currentInjector; $i++) {
                        // because of the skip back, we need to add one more
                        // for uninitialized injectors. I'm not exactly
                        // sure why this is the case, but I think it has to
                        // do with the fact that we're decrementing skips
                        // before re-checking text
                        if (!$this->injectors[$i]->skip) $this->injectors[$i]->skip++;
                        $this->injectors[$i]->skip += $offset;
                    }
                }
            }
            // ensure that we reprocess these tokens with the other injectors
            --$this->inputIndex;
            
        } elseif ($token) {
            // regular case
            $this->swap($token);
            if ($token instanceof HTMLPurifier_Token_Start) {
                $this->currentNesting[] = $token;
            } elseif ($token instanceof HTMLPurifier_Token_End) {
                // not actually used
                $token->start = array_pop($this->currentNesting);
            }
        } else {
            $this->remove();
        }
    }
    
}

