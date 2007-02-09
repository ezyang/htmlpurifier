<?php

/**
 * Structure that stores an HTML element definition. Used by
 * HTMLPurifier_HTMLDefinition and HTMLPurifier_HTMLModule.
 */
class HTMLPurifier_ElementDef
{
    
    /**
     * Associative array of attribute name to HTMLPurifier_AttrDef
     * @note Before being processed by HTMLPurifier_AttrCollections
     *       when modules are finalized during
     *       HTMLPurifier_HTMLDefinition->setup(), this array may also
     *       contain an array at index 0 that indicates which attribute
     *       collections to load into the full array. It may also
     *       contain string indentifiers in lieu of HTMLPurifier_AttrDef,
     *       see HTMLPurifier_AttrTypes on how they are expanded during
     *       HTMLPurifier_HTMLDefinition->setup() processing.
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
     * HTMLPurifier_ChildDef of this tag.
     * @public
     */
    var $child;
    
    /**
     * Abstract string representation of internal ChildDef rules. See
     * HTMLPurifier_ContentSets for how this is parsed and then transformed
     * into an HTMLPurifier_ChildDef.
     * @public
     */
    var $content_model;
    
    /**
     * Value of $child->type, used to determine which ChildDef to use,
     * used in combination with $content_model.
     * @public
     */
    var $content_model_type;
    
    
    
    /**
     * Lookup table of tags that close this tag. Used during parsing
     * to make sure we don't attempt to nest unclosed tags.
     * @public
     */
    var $auto_close = array();
    
    /**
     * Does the element have a content model (#PCDATA | Inline)*? This
     * is important for chameleon ins and del processing in 
     * HTMLPurifier_ChildDef_Chameleon.
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
