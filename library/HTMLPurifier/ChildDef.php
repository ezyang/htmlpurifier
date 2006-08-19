<?php

// HTMLPurifier_ChildDef and inheritance have three types of output:
// true = leave nodes as is
// false = delete parent node and all children
// array(...) = replace children nodes with these

// this is the hardest one to implement. We'll use fancy regexp tricks
// right now, we only expect it to return TRUE or FALSE (it won't attempt
// to fix the tree)

// we may end up writing custom code for each HTML case
// in order to make it self correcting

HTMLPurifier_ConfigDef::define(
    'Core', 'EscapeInvalidChildren', false,
    'When true, a child is found that is not allowed in the context of the '.
    'parent element will be transformed into text as if it were ASCII. When '.
    'false, that element and all internal tags will be dropped, though text '.
    'will be preserved.  There is no option for dropping the element but '.
    'preserving child nodes.'
);

/**
 * Defines allowed child nodes and validates tokens against it.
 */
class HTMLPurifier_ChildDef
{
    /**
     * Type of child definition, usually right-most part of class name lowercase
     * 
     * Used occasionally in terms of context.  Possible values include
     * custom, required, optional and empty.
     */
    var $type;
    
    /**
     * Bool that indicates whether or not an empty array of children is okay
     * 
     * This is necessary for redundant checking when changes affecting
     * a child node may cause a parent node to now be disallowed.
     */
    var $allow_empty;
    
    /**
     * Validates nodes according to definition and returns modification.
     * 
     * @warning $context is NOT HTMLPurifier_AttrContext
     * @param $tokens_of_children Array of HTMLPurifier_Token
     * @param $config HTMLPurifier_Config object
     * @param $context String context indicating inline, block or unknown
     * @return bool true to leave nodes as is
     * @return bool false to remove parent node
     * @return array of replacement child tokens
     */
    function validateChildren($tokens_of_children, $config, $context) {
        trigger_error('Call to abstract function', E_USER_ERROR);
    }
}

/**
 * Custom validation class, accepts DTD child definitions
 * 
 * @warning Currently this class is an all or nothing proposition, that is,
 *          it will only give a bool return value.  Table is the only
 *          child definition that uses this class, and we ought to give
 *          it a dedicated one.
 */
class HTMLPurifier_ChildDef_Custom extends HTMLPurifier_ChildDef
{
    var $type = 'custom';
    var $allow_empty = false;
    /**
     * Allowed child pattern as defined by the DTD
     */
    var $dtd_regex;
    /**
     * PCRE regex derived from $dtd_regex
     * @private
     */
    var $_pcre_regex;
    /**
     * @param $dtd_regex Allowed child pattern from the DTD
     */
    function HTMLPurifier_ChildDef_Custom($dtd_regex) {
        $this->dtd_regex = $dtd_regex;
        $this->_compileRegex();
    }
    /**
     * Compiles the PCRE regex from a DTD regex ($dtd_regex to $_pcre_regex)
     */
    function _compileRegex() {
        $raw = str_replace(' ', '', $this->dtd_regex);
        if ($raw{0} != '(') {
            $raw = "($raw)";
        }
        $reg = str_replace(',', ',?', $raw);
        $reg = preg_replace('/([#a-zA-Z0-9_.-]+)/', '(,?\\0)', $reg);
        $this->_pcre_regex = $reg;
    }
    function validateChildren($tokens_of_children, $config, $context) {
        $list_of_children = '';
        $nesting = 0; // depth into the nest
        foreach ($tokens_of_children as $token) {
            if (!empty($token->is_whitespace)) continue;
            
            $is_child = ($nesting == 0); // direct
            
            if ($token->type == 'start') {
                $nesting++;
            } elseif ($token->type == 'end') {
                $nesting--;
            }
            
            if ($is_child) {
                $list_of_children .= $token->name . ',';
            }
        }
        $list_of_children = rtrim($list_of_children, ',');
        
        $okay =
            preg_match(
                '/^'.$this->_pcre_regex.'$/',
                $list_of_children
            );
        
        return (bool) $okay;
    }
}

/**
 * Definition that allows a set of elements, but disallows empty children.
 */
class HTMLPurifier_ChildDef_Required extends HTMLPurifier_ChildDef
{
    /**
     * Lookup table of allowed elements.
     */
    var $elements = array();
    /**
     * @param $elements List of allowed element names (lowercase).
     */
    function HTMLPurifier_ChildDef_Required($elements) {
        if (is_string($elements)) {
            $elements = str_replace(' ', '', $elements);
            $elements = explode('|', $elements);
        }
        $elements = array_flip($elements);
        foreach ($elements as $i => $x) $elements[$i] = true;
        $this->elements = $elements;
        $this->gen = new HTMLPurifier_Generator();
    }
    var $allow_empty = false;
    var $type = 'required';
    function validateChildren($tokens_of_children, $config, $context) {
        // if there are no tokens, delete parent node
        if (empty($tokens_of_children)) return false;
        
        // the new set of children
        $result = array();
        
        // current depth into the nest
        $nesting = 0;
        
        // whether or not we're deleting a node
        $is_deleting = false;
        
        // whether or not parsed character data is allowed
        // this controls whether or not we silently drop a tag
        // or generate escaped HTML from it
        $pcdata_allowed = isset($this->elements['#PCDATA']);
        
        // a little sanity check to make sure it's not ALL whitespace
        $all_whitespace = true;
        
        // some configuration
        $escape_invalid_children = $config->get('Core', 'EscapeInvalidChildren');
        
        foreach ($tokens_of_children as $token) {
            if (!empty($token->is_whitespace)) {
                $result[] = $token;
                continue;
            }
            $all_whitespace = false; // phew, we're not talking about whitespace
            
            $is_child = ($nesting == 0);
            
            if ($token->type == 'start') {
                $nesting++;
            } elseif ($token->type == 'end') {
                $nesting--;
            }
            
            if ($is_child) {
                $is_deleting = false;
                if (!isset($this->elements[$token->name])) {
                    $is_deleting = true;
                    if ($pcdata_allowed && $token->type == 'text') {
                        $result[] = $token;
                    } elseif ($pcdata_allowed && $escape_invalid_children) {
                        $result[] = new HTMLPurifier_Token_Text(
                            $this->gen->generateFromToken($token, $config)
                        );
                    }
                    continue;
                }
            }
            if (!$is_deleting || ($pcdata_allowed && $token->type == 'text')) {
                $result[] = $token;
            } elseif ($pcdata_allowed && $escape_invalid_children) {
                $result[] =
                    new HTMLPurifier_Token_Text(
                        $this->gen->generateFromToken( $token, $config )
                    );
            } else {
                // drop silently
            }
        }
        if (empty($result)) return false;
        if ($all_whitespace) return false;
        if ($tokens_of_children == $result) return true;
        return $result;
    }
}

/**
 * Definition that allows a set of elements, and allows no children.
 * @note This is a hack to reuse code from HTMLPurifier_ChildDef_Required,
 *       really, one shouldn't inherit from the other.  Only altered behavior
 *       is to overload a returned false with an array.  Thus, it will never
 *       return false.
 */
class HTMLPurifier_ChildDef_Optional extends HTMLPurifier_ChildDef_Required
{
    var $allow_empty = true;
    var $type = 'optional';
    function validateChildren($tokens_of_children, $config, $context) {
        $result = parent::validateChildren($tokens_of_children, $config, $context);
        if ($result === false) return array();
        return $result;
    }
}

/**
 * Definition that disallows all elements.
 * @warning validateChildren() in this class is actually never called, because
 *          empty elements are corrected in HTMLPurifier_Strategy_MakeWellFormed
 *          before child definitions are parsed in earnest by
 *          HTMLPurifier_Strategy_FixNesting.
 */
class HTMLPurifier_ChildDef_Empty extends HTMLPurifier_ChildDef
{
    var $allow_empty = true;
    var $type = 'empty';
    function HTMLPurifier_ChildDef_Empty() {}
    function validateChildren($tokens_of_children, $config, $context) {
        return array();
    }
}

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
     */
    var $inline;
    /**
     * Instance of the definition object to use when block.
     */
    var $block;
    
    /**
     * @param $inline List of elements to allow when inline.
     * @param $block List of elements to allow when block.
     */
    function HTMLPurifier_ChildDef_Chameleon($inline, $block) {
        $this->inline = new HTMLPurifier_ChildDef_Optional($inline);
        $this->block  = new HTMLPurifier_ChildDef_Optional($block);
    }
    
    function validateChildren($tokens_of_children, $config, $context) {
        switch ($context) {
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