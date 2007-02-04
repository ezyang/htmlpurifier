<?php

/**
 * Structure that stores an HTML element definition. Used by
 * HTMLPurifier_HTMLDefinition and HTMLPurifier_HTMLModule.
 */
class HTMLPurifier_ElementDef
{
    
    /**
     * Associative array of attribute name to HTMLPurifier_AttrDef
     * @public
     */
    var $attr = array();
    
    /**
     * List of tag's HTMLPurifier_AttrTransform to be done before validation
     * @public
     */
    var $attr_transform_pre = array();
    
    /**
     * List of tag's HTMLPurifier_AttrTransform to be done after validation
     * @public
     */
    var $attr_transform_post = array();
    
    /**
     * Lookup table of tags that close this tag.
     * @public
     */
    var $auto_close = array();
    
    /**
     * HTMLPurifier_ChildDef of this tag.
     * @public
     */
    var $child;
    
    /**
     * Abstract string representation of internal ChildDef rules
     * @public
     */
    var $content_model;
    
    /**
     * Value of $child->type, used to determine which ChildDef to use
     * @public
     */
    var $content_model_type;
    
    /**
     * Does the element have a content model (#PCDATA | Inline)*? This
     * is important for chameleon ins and del processing.
     * @public
     */
    var $descendants_are_inline;
    
    /**
     * Lookup table of tags excluded from all descendants of this tag.
     * @public
     */
    var $excludes = array();
    
}

?>
