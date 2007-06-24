<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/HTMLDefinition.php';
require_once 'HTMLPurifier/Generator.php';

require_once 'HTMLPurifier/Injector/AutoParagraph.php';
require_once 'HTMLPurifier/Injector/Linkify.php';

HTMLPurifier_ConfigSchema::define(
    'Core', 'AutoParagraph', false, 'bool', '
<p>
  This directive will cause HTML Purifier to automatically paragraph text
  in the document fragment root based on two newlines and block tags.
  This directive has been available since 2.0.1.
</p>
'
);

HTMLPurifier_ConfigSchema::define(
    'Core', 'AutoLinkify', false, 'bool', '
<p>
  This directive will cause HTML Purifier to automatically linkify
  text that looks like URLs. This directive has been available since
  2.0.1.
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
        
        // -- begin INJECTOR --
        // factor this stuff out to its own class
        
        $injector = array();
        $injector_skip = array();
        
        if ($config->get('Core', 'AutoParagraph')) {
            $injector[] = new HTMLPurifier_Injector_AutoParagraph();
            // decrement happens first, so set to one so we start at zero
            $injector_skip[] = 1;
        }
        
        if ($config->get('Core', 'AutoLinkify')) {
            $injector[] = new HTMLPurifier_Injector_Linkify();
            $injector_skip[] = 1;
        }
        
        // array index of the injector that resulted in an array
        // substitution. This enables processTokens() to know which
        // injectors are affected by the added tokens and which are
        // not (namely, the ones after the current injector are not
        // affected)
        $current_injector = false;
        
        $context->register('Injector', $injector);
        $context->register('CurrentInjector', $current_injector);
        
        // number of tokens to skip + 1
        // before processing, this gets decremented: if it equals zero,
        // it means the injector is active and is processing tokens, if
        // it is greater than zero, then it is inactive, presumably having
        // been the source of the tokens
        $context->register('InjectorSkip', $injector_skip);
        
        // -- end INJECTOR --
        
        for ($tokens_index = 0; isset($tokens[$tokens_index]); $tokens_index++) {
            
            // if all goes well, this token will be passed through unharmed
            $token = $tokens[$tokens_index];
            
            foreach ($injector as $i => $x) {
                if ($injector_skip[$i] > 0) $injector_skip[$i]--;
            }
            
            // quick-check: if it's not a tag, no need to process
            if (empty( $token->is_tag )) {
                
                // duplicated with handleStart
                if ($token->type === 'text') {
                     foreach ($injector as $i => $x) {
                         if (!$injector_skip[$i]) {
                             $x->handleText($token, $config, $context);
                         }
                         if (is_array($token)) {
                             $current_injector = $i;
                             break;
                         }
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
                    if (!isset($parent_info->child->elements[$token->name])) {
                        // close the parent, then append the token
                        $result[] = new HTMLPurifier_Token_End($parent->name);
                        $result[] = $token;
                        $current_nesting[] = $token;
                        continue;
                    }
                    
                    $current_nesting[] = $parent; // undo the pop
                }
                
                // injectors
                foreach ($injector as $i => $x) {
                    if (!$injector_skip[$i]) {
                        $x->handleStart($token, $config, $context);
                    }
                    if (is_array($token)) {
                        $current_injector = $i;
                        break;
                    }
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
        
        $context->destroy('Injector');
        $context->destroy('CurrentInjector');
        $context->destroy('InjectorSkip');
        
        return $result;
    }
    
    function processToken($token, $config, &$context) {
        if (is_array($token)) {
            // the original token was overloaded by an injector, time
            // to some fancy acrobatics
            
            $tokens              =& $context->get('InputTokens');
            $tokens_index        =& $context->get('InputIndex');
            // $tokens_index is decremented so that the entire set gets
            // re-processed
            array_splice($tokens, $tokens_index--, 1, $token);
            
            // adjust the injector skips based on the array substitution
            $injector_skip    =& $context->get('InjectorSkip');
            $current_injector =& $context->get('CurrentInjector');
            
            $offset = count($token) + 1;
            for ($i = 0; $i <= $current_injector; $i++) {
                $injector_skip[$i] += $offset;
            }
            
        } elseif ($token) {
            // regular case
            $result =& $context->get('OutputTokens');
            $current_nesting =& $context->get('CurrentNesting');
            $result[] = $token;
            if ($token->type == 'start') {
                $current_nesting[] = $token;
            } elseif ($token->type == 'end') {
                // theoretical: this code doesn't get run because performing
                // the calculations inline is more efficient, and
                // end tokens (currently) do not cause a handler invocation
                array_pop($current_nesting);
            }
        }
    }
    
}

?>