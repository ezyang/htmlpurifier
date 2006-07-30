<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/Definition.php';

class HTMLPurifier_Strategy_FixNesting extends HTMLPurifier_Strategy
{
    
    var $definition;
    
    function HTMLPurifier_Strategy_FixNesting() {
        $this->definition = HTMLPurifier_Definition::instance();
    }
    
    function execute($tokens) {
        // insert implicit "parent" node, will be removed at end
        array_unshift($tokens, new HTMLPurifier_Token_Start('div'));
        $tokens[] = new HTMLPurifier_Token_End('div');
        
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
            
            // have DTD child def validate children
            $child_def = $this->definition->info['child'][$tokens[$i]->name];
            $result = $child_def->validateChildren($child_tokens);
            
            // process result
            if ($result === true) {
                
                // leave the nodes as is
                
            } elseif($result === false) {
                
                // WARNING WARNING WARNING!!!
                // While for the original DTD, there will never be
                // cascading removal, more complex ones may have such
                // a problem.
                
                // If you modify the info array such that an element
                // that requires children may contain a child that requires
                // children, you need to also scroll back and re-check that
                // elements parent node
                
                $length = $j - $i + 1;
                
                // remove entire node
                array_splice($tokens, $i, $length);
                
                // change size
                $size -= $length;
                
                // ensure that we scroll to the next node
                $i--;
                
            } else {
                
                $length = $j - $i - 1;
                
                // replace node with $result
                array_splice($tokens, $i + 1, $length, $result);
                
                // change size
                $size -= $length;
                $size += count($result);
                
            }
            
            // scroll to next node
            $i++;
            while ($i < $size and $tokens[$i]->type != 'start') $i++;
            
        }
        
        // remove implicit divs
        array_shift($tokens);
        array_pop($tokens);
        
        return $tokens;
        
    }
    
}

?>