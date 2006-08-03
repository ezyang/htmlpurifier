<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/Definition.php';

/**
 * Takes a well formed list of tokens and fixes their nesting.
 * 
 * HTML elements dictate which elements are allowed to be their children,
 * for example, you can't have a p tag in a span tag.  Other elements have
 * much more rigorous definitions: tables, for instance, require a specific
 * order for their elements.  There are also constraints not expressible by
 * document type definitions, such as the chameleon nature of ins/del
 * tags and global child exclusions.
 * 
 * The first major objective of this strategy is to iterate through all the
 * nodes (not tokens) of the list of tokens and determine whether or not
 * their children conform to the element's definition.  If they do not, the
 * child definition may optionally supply an amended list of elements that
 * is valid or require that the entire node be deleted (and the previous
 * node rescanned).
 * 
 * The second objective is to ensure that explicitly excluded elements of
 * an element do not appear in its children.  Code that accomplishes this
 * task is pervasive through the strategy, though the two are distinct tasks
 * and could, theoretically, be seperated (although it's not recommended).
 * 
 * @note Whether or not unrecognized children are silently dropped or
 *       translated into text depends on the child definitions.
 * 
 * @todo Enable nodes to be bubbled out of the structure.
 */

class HTMLPurifier_Strategy_FixNesting extends HTMLPurifier_Strategy
{
    
    var $definition;
    
    function HTMLPurifier_Strategy_FixNesting() {
        $this->definition = HTMLPurifier_Definition::instance();
    }
    
    function execute($tokens) {
        
        // insert implicit "parent" node, will be removed at end
        $parent_name = $this->definition->info_parent;
        array_unshift($tokens, new HTMLPurifier_Token_Start($parent_name));
        $tokens[] = new HTMLPurifier_Token_End($parent_name);
        
        // stack that contains the indexes of all parents,
        // $stack[count($stack)-1] being the current parent
        $stack = array();
        
        // stack that contains all elements that are excluded
        $exclude_stack = array();
        
        for ($i = 0, $size = count($tokens) ; $i < $size; ) {
            
            $child_tokens = array();
            
            // scroll to the end of this node, and report number
            for ($j = $i, $depth = 0; ; $j++) {
                if ($tokens[$j]->type == 'start') {
                    $depth++;
                    // skip token assignment on first iteration
                    if ($depth == 1) continue;
                } elseif ($tokens[$j]->type == 'end') {
                    $depth--;
                    // skip token assignment on last iteration
                    if ($depth == 0) break;
                }
                $child_tokens[] = $tokens[$j];
            }
            
            // $i is index of start token
            // $j is index of end token
            
            // calculate parent information
            if ($count = count($stack)) {
                $parent_index = $stack[$count-1];
                $parent_name  = $tokens[$parent_index]->name;
                $parent_def   = $this->definition->info[$parent_name];
            } else {
                $parent_index = $parent_name = $parent_def = null;
            }
            
            // calculate context
            if (isset($parent_def)) {
                $context = $parent_def->type;
            } else {
                $context = 'unknown';
            }
            
            // determine whether or not element is excluded
            $excluded = false;
            if (!empty($exclude_stack)) {
                foreach ($exclude_stack as $lookup) {
                    if (isset($lookup[$tokens[$i]->name])) {
                        $excluded = true;
                        break;
                    }
                }
            }
            
            if ($excluded) {
                $result = false;
            } else {
                // DEFINITION CALL
                $def = $this->definition->info[$tokens[$i]->name];
                
                $child_def = $def->child;
                
                // have DTD child def validate children
                $result = $child_def->validateChildren($child_tokens, $context);
                
                // determine whether or not this element has any exclusions
                $excludes = $def->excludes;
            }
            
            // process result
            if ($result === true) {
                
                // leave the node as is
                
                // register start token as a parental node start
                $stack[] = $i;
                
                // register exclusions if there are any
                if (!empty($excludes)) $exclude_stack[] = $excludes;
                
                // move cursor to next possible start node
                $i++;
                
            } elseif($result === false) {
                
                $length = $j - $i + 1;
                
                // remove entire node
                array_splice($tokens, $i, $length);
                
                // change size
                $size -= $length;
                
                // there is no start token to register,
                // current node is now the next possible start node
                // unless it turns out that we need to do a double-check
                
                if (!$parent_def->child->allow_empty) {
                    // we need to do a double-check
                    $i = $parent_index;
                }
                
            } else {
                
                $length = $j - $i - 1;
                
                // replace node with $result
                array_splice($tokens, $i + 1, $length, $result);
                
                // change size
                $size -= $length;
                $size += count($result);
                
                // register start token as a parental node start
                $stack[] = $i;
                
                // register exclusions if there are any
                if (!empty($excludes)) $exclude_stack[] = $excludes;
                
                // move cursor to next possible start node
                $i++;
                
            }
            
            // We assume, at this point, that $i is the index of the token
            // that is the first possible new start point for a node.
            
            // Test if the token indeed is a start tag, if not, move forward
            // and test again.
            while ($i < $size and $tokens[$i]->type != 'start') {
                if ($tokens[$i]->type == 'end') {
                    // pop a token index off the stack if we ended a node
                    array_pop($stack);
                    // pop an exclusion lookup off exclusion stack if
                    // we ended node and that node had exclusions
                    if ($this->definition->info[$tokens[$i]->name]->excludes) {
                        array_pop($exclude_stack);
                    }
                }
                $i++;
            }
            
        }
        
        // remove implicit divs
        array_shift($tokens);
        array_pop($tokens);
        
        return $tokens;
        
    }
    
}

?>