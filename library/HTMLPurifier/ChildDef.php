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
    'false, that element (and all its descendants) will be silently dropped.'
);

class HTMLPurifier_ChildDef
{
    var $type;
    var $allow_empty;
    function validateChildren($tokens_of_children) {
        trigger_error('Call to abstract function', E_USER_ERROR);
    }
}

class HTMLPurifier_ChildDef_Custom extends HTMLPurifier_ChildDef
{
    var $type = 'custom';
    var $allow_empty = false;
    var $dtd_regex;
    var $_pcre_regex;
    function HTMLPurifier_ChildDef_Custom($dtd_regex) {
        $this->dtd_regex = $dtd_regex;
        $this->_compileRegex();
    }
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

class HTMLPurifier_ChildDef_Required extends HTMLPurifier_ChildDef
{
    var $elements = array();
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
                    if ($pcdata_allowed && $escape_invalid_children) {
                        $result[] = new HTMLPurifier_Token_Text(
                            $this->gen->generateFromToken($token)
                        );
                    }
                    continue;
                }
            }
            if (!$is_deleting) {
                $result[] = $token;
            } elseif ($pcdata_allowed && $escape_invalid_children) {
                $result[] =
                    new HTMLPurifier_Token_Text(
                        $this->gen->generateFromToken( $token )
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

// only altered behavior is that it returns an empty array
// instead of a false (to delete the node)
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

// placeholder
class HTMLPurifier_ChildDef_Empty extends HTMLPurifier_ChildDef
{
    var $allow_empty = true;
    var $type = 'empty';
    function HTMLPurifier_ChildDef_Empty() {}
    function validateChildren($tokens_of_children, $config, $context) {
        return false;
    }
}

class HTMLPurifier_ChildDef_Chameleon extends HTMLPurifier_ChildDef
{
    
    var $inline;
    var $block;
    
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