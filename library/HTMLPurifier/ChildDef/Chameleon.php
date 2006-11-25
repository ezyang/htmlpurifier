<?php

require_once 'HTMLPurifier/ChildDef.php';

/**
 * Definition that uses different definitions depending on context.
 * 
 * The del and ins tags are notable because they allow different types of
 * elements depending on whether or not they're in a block or inline context.
 * Chameleon allows this behavior to happen by using two different
 * definitions depending on context.  While this somewhat generalized,
 * it is specifically intended for those two tags.
 */
class HTMLPurifier_ChildDef_Chameleon extends HTMLPurifier_ChildDef
{
    
    /**
     * Instance of the definition object to use when inline. Usually stricter.
     * @public
     */
    var $inline;
    
    /**
     * Instance of the definition object to use when block.
     * @public
     */
    var $block;
    
    var $type = 'chameleon';
    
    /**
     * @param $inline List of elements to allow when inline.
     * @param $block List of elements to allow when block.
     */
    function HTMLPurifier_ChildDef_Chameleon($inline, $block) {
        $this->inline = new HTMLPurifier_ChildDef_Optional($inline);
        $this->block  = new HTMLPurifier_ChildDef_Optional($block);
    }
    
    function validateChildren($tokens_of_children, $config, &$context) {
        $parent_type = $context->get('ParentType');
        switch ($parent_type) {
            case 'unknown':
            case 'inline':
                $result = $this->inline->validateChildren(
                    $tokens_of_children, $config, $context);
                break;
            case 'block':
                $result = $this->block->validateChildren(
                    $tokens_of_children, $config, $context);
                break;
            default:
                trigger_error('Invalid context', E_USER_ERROR);
                return false;
        }
        return $result;
    }
}

?>