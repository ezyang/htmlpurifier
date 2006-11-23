<?php

require_once 'HTMLPurifier/ChildDef/Required.php';

/**
 * Takes the contents of blockquote when in strict and reformats for validation.
 * 
 * From XHTML 1.0 Transitional to Strict, there is a notable change where 
 */
class   HTMLPurifier_ChildDef_StrictBlockquote
extends HTMLPurifier_ChildDef_Required
{
    var $allow_empty = true;
    var $type = 'strictblockquote';
    var $init = false;
    function HTMLPurifier_ChildDef_StrictBlockquote() {}
    function validateChildren($tokens_of_children, $config, &$context) {
        
        $def = $config->getHTMLDefinition();
        if (!$this->init) {
            // allow all inline elements
            $this->elements = $def->info_flow_elements;
            $this->elements['#PCDATA'] = true;
            $this->init = true;
        }
        
        $result = parent::validateChildren($tokens_of_children, $config, $context);
        if ($result === false) return array();
        if ($result === true) $result = $tokens_of_children;
        
        $block_wrap_start = new HTMLPurifier_Token_Start($def->info_block_wrapper);
        $block_wrap_end   = new HTMLPurifier_Token_End(  $def->info_block_wrapper);
        $is_inline = false;
        $depth = 0;
        $ret = array();
        
        // assuming that there are no comment tokens
        foreach ($result as $i => $token) {
            $token = $result[$i];
            // ifs are nested for readability
            if (!$is_inline) {
                if (!$depth) {
                     if (($token->type == 'text') ||
                         ($def->info[$token->name]->type == 'inline')) {
                        $is_inline = true;
                        $ret[] = $block_wrap_start;
                     }
                }
            } else {
                if (!$depth) {
                    // starting tokens have been inline text / empty
                    if ($token->type == 'start' || $token->type == 'empty') {
                        if ($def->info[$token->name]->type == 'block') {
                            // ended
                            $ret[] = $block_wrap_end;
                            $is_inline = false;
                        }
                    }
                }
            }
            $ret[] = $token;
            if ($token->type == 'start') $depth++;
            if ($token->type == 'end')   $depth--;
        }
        if ($is_inline) $ret[] = $block_wrap_end;
        return $ret;
    }
}

?>