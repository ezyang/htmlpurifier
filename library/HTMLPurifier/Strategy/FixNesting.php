<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/Definition.php';

// EXTRA: provide a mechanism for elements to be bubbled OUT of a node
// or "Replace Nodes while including the parent nodes too"

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
            
            
            // DEFINITION CALL
            $child_def = $this->definition->info[$tokens[$i]->name]->child;
            
            // have DTD child def validate children
            $result = $child_def->validateChildren($child_tokens);
            
            // process result
            if ($result === true) {
                
                // leave the node as is
                
                // register start token as a parental node start
                $stack[] = $i;
                
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
                
                $parent_index = $stack[count($stack)-1];
                $parent_name  = $tokens[$parent_index]->name;
                $parent_def   = $this->definition->info[$parent_name];
                
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
                
                // move cursor to next possible start node
                $i++;
                
            }
            
            // We assume, at this point, that $i is the index of the token
            // that is the first possible new start point for a node.
            
            // Test if the token indeed is a start tag, if not, move forward
            // and test again.
            while ($i < $size and $tokens[$i]->type != 'start') {
                // pop a token index off the stack if we ended a node
                if ($tokens[$i]->type == 'end') array_pop($stack);
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