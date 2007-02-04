<?php

/**
 * Represents an XHTML 1.1 module, with information on elements, tags
 * and attributes.
 * @note Even though this is technically XHTML 1.1, it is also used for
 *       regular HTML parsing. We are using modulization as a convenient
 *       way to represent the internals of HTMLDefinition, and our
 *       implementation is by no means conforming and does not directly
 *       use the normative DTDs or XML schemas.
 */

class HTMLPurifier_HTMLModule
{
    /**
     * List of elements that the module implements.
     * @note This is only for convention, as a module will often loop
     *       through the $elements array to define HTMLPurifier_ElementDef
     *       in the $info array.
     * @protected
     */
    var $elements = array();
    
    /**
     * Associative array of element names to element definitions.
     * Some definitions may be incomplete, to be merged in later
     * with the full definition.
     * @public
     */
    var $info = array();
    
    /**
     * Associative array of content set names to content set additions.
     * This is commonly used to, say, add an A element to the Inline
     * content set.
     * @public
     */
    var $content_sets = array();
    
    /**
     * Associative array of attribute collection names to attribute
     * collection additions. More rarely used for adding attributes to
     * the global collections. Example is the StyleAttribute module adding
     * the style attribute to the Core.
     * @public
     */
    var $attr_collection = array();
    
    /**
     * Boolean flag that indicates whether or not getChildDef is implemented.
     * For optimization reasons: may save a call to a function. Be sure
     * to set it if you do implement getChildDef(), otherwise it will have
     * no effect!
     * @public
     */
    var $defines_child_def = false;
    
    /**
     * Retrieves a proper HTMLPurifier_ChildDef subclass based on 
     * content_model and content_model_type member variables of
     * the HTMLPurifier_ElementDef class. There is a similar function
     * in HTMLPurifier_HTMLDefinition.
     * @param $def HTMLPurifier_ElementDef instance
     * @return HTMLPurifier_ChildDef subclass
     * @public
     */
    function getChildDef($def) {return false;}
}

?>