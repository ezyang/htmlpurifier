<?php

require_once 'HTMLPurifier/HTMLDefinition.php';

require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrCollections.php';

// we'll manage loading extremely commonly used attr definitions
require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/AttrDef/Enum.php';

// technically speaking, these includes would be more appropriate for
// other modules, but we're going to include all the common ones. A
// custom one would have to be fed in as an actual object
require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/ChildDef/Empty.php';
require_once 'HTMLPurifier/ChildDef/Required.php';
require_once 'HTMLPurifier/ChildDef/Optional.php';
require_once 'HTMLPurifier/ChildDef/StrictBlockquote.php';

require_once 'HTMLPurifier/HTMLModule.php';
require_once 'HTMLPurifier/HTMLModule/Text.php';
require_once 'HTMLPurifier/HTMLModule/Hypertext.php';
require_once 'HTMLPurifier/HTMLModule/List.php';
require_once 'HTMLPurifier/HTMLModule/Presentation.php';
require_once 'HTMLPurifier/HTMLModule/Edit.php';
require_once 'HTMLPurifier/HTMLModule/Bdo.php';
require_once 'HTMLPurifier/HTMLModule/Tables.php';
require_once 'HTMLPurifier/HTMLModule/Image.php';
require_once 'HTMLPurifier/HTMLModule/StyleAttribute.php';

/**
 * Next-generation HTML definition that will supplant HTMLPurifier_HTMLDefinition
 */
class HTMLPurifier_XHTMLDefinition extends HTMLPurifier_HTMLDefinition
{
    
    /**
     * Array of HTMLPurifier_Module instances, indexed by module name
     * @public
     */
    var $modules = array();
    
    /**
     * Instance of HTMLPurifier_AttrTypes
     * @public
     */
    var $attr_types;
    
    /**
     * Instance of HTMLPurifier_AttrCollections
     * @public
     */
    var $attr_collections;
    
    /**
     * Performs low-cost, preliminary initialization.
     * @param $config Instance of HTMLPurifier_Config
     */
    function HTMLPurifier_XHTMLDefinition($config) {
        
        $this->modules['Text']          = new HTMLPurifier_HTMLModule_Text();
        $this->modules['Hypertext']     = new HTMLPurifier_HTMLModule_Hypertext();
        $this->modules['List']          = new HTMLPurifier_HTMLModule_List();
        $this->modules['Presentation']  = new HTMLPurifier_HTMLModule_Presentation();
        $this->modules['Edit']          = new HTMLPurifier_HTMLModule_Edit();
        $this->modules['Bdo']           = new HTMLPurifier_HTMLModule_Bdo();
        $this->modules['Tables']        = new HTMLPurifier_HTMLModule_Tables();
        $this->modules['Image']         = new HTMLPurifier_HTMLModule_Image();
        $this->modules['StyleAttribute']= new HTMLPurifier_HTMLModule_StyleAttribute();
        
        $this->attr_types = new HTMLPurifier_AttrTypes();
        $this->attr_collections = new HTMLPurifier_AttrCollections();
        
    }
    
    /**
     * Processes internals into form usable by HTMLPurifier internals. 
     * Modifying the definition after calling this function should not
     * be done.
     * @param $config Instance of HTMLPurifier_Config
     */
    function setup($config) {
        
        // perform attribute collection substitutions
        $this->attr_collections->setup($this->attr_types, $this->modules);
        
        // populate content_sets based on module hints
        $content_sets = array();
        foreach ($this->modules as $module_i => $module) {
            foreach ($module->content_sets as $key => $value) {
                if (isset($content_sets[$key])) {
                    // add it into the existing content set
                    $content_sets[$key] = $content_sets[$key] . ' | ' . $value;
                } else {
                    $content_sets[$key] = $value;
                }
            }
        }
        
        // perform content_set expansions
        foreach ($content_sets as $i => $set) {
            // only performed once, so infinite recursion is not
            // a problem, you'll just have a stray $Set lying around
            // at the end
            $content_sets[$i] =
                str_replace(
                    array_keys($content_sets),
                    array_values($content_sets),
                $set);
        }
        // define convenient variables
        $content_sets_keys   = array_keys($content_sets);
        $content_sets_values = array_values($content_sets);
        foreach ($content_sets as $name => $set) {
            $this->info_content_sets[$name] = $this->convertToLookup($set);
        }
        
        foreach ($this->modules as $module_i => $module) {
            foreach ($module->info as $name => $def) {
                $def =& $this->modules[$module_i]->info[$name];
                
                // attribute value expansions
                
                $this->attr_collections->performInclusions($def->attr);
                $this->attr_collections->expandIdentifiers(
                    $def->attr, $this->attr_types);
                
                // perform content model expansions
                $content_model = $def->content_model;
                if (is_string($content_model)) {
                    if (strpos($content_model, 'Inline') !== false) {
                        if ($name != 'del' && $name != 'ins') {
                            // this is for you, ins/del
                            $def->descendants_are_inline = true;
                        }
                    }
                    $def->content_model = str_replace(
                        $content_sets_keys, $content_sets_values, $content_model);
                }
                
                // get child def from content model
                $def->child = $this->getChildDef($def);
                
                // setup info
                $this->info[$name] = $def;
                if ($this->info_parent == $name) {
                    $this->info_parent_def = $this->info[$name];
                }
            }
        }
        
        $this->setupAttrTransform($config);
        $this->setupBlockWrapper($config);
        $this->setupParent($config);
        
    }
    
    /**
     * Sets up attribute transformations
     * @param $config Instance of HTMLPurifier_Config
     */
    function setupAttrTransform($config) {
        $this->info_attr_transform_post[] = new HTMLPurifier_AttrTransform_Lang();
    }
    
    /**
     * Sets up block wrapper based on config
     * @param $config Instance of HTMLPurifier_Config
     */
    function setupBlockWrapper($config) {
        $block_wrapper = $config->get('HTML', 'BlockWrapper');
        if (isset($this->info_content_sets['Block'][$block_wrapper])) {
            $this->info_block_wrapper = $block_wrapper;
        } else {
            trigger_error('Cannot use non-block element as block wrapper.',
                E_USER_ERROR);
        }
    }
    
    /**
     * Sets up parent of fragment based on config
     * @param $config Instance of HTMLPurifier_Config
     */
    function setupParent($config) {
        $parent = $config->get('HTML', 'Parent');
        if (isset($this->info[$parent])) {
            $this->info_parent = $parent;
        } else {
            trigger_error('Cannot use unrecognized element as parent.',
                E_USER_ERROR);
        }
        $this->info_parent_def = $this->info[$this->info_parent];
    }
    
    /**
     * Instantiates a ChildDef based on content_model and content_model_type
     * member variables in HTMLPurifier_ElementDef
     * @note This will also defer to modules for custom HTMLPurifier_ChildDef
     *       subclasses that need content set expansion
     * @param $def HTMLPurifier_ElementDef to have ChildDef extracted
     * @return HTMLPurifier_ChildDef corresponding to ElementDef
     */
    function getChildDef($def) {
        $value = $def->content_model;
        if (is_object($value)) return $value; // direct object, return
        switch ($def->content_model_type) {
            case 'required':
                return new HTMLPurifier_ChildDef_Required($value);
            case 'optional':
                return new HTMLPurifier_ChildDef_Optional($value);
            case 'empty':
                return new HTMLPurifier_ChildDef_Empty();
            case 'strictblockquote':
                return new HTMLPurifier_ChildDef_StrictBlockquote($value);
            case 'custom':
                return new HTMLPurifier_ChildDef_Custom($value);
        }
        // defer to modules, see if they know what child_def to use
        foreach ($this->modules as $module) {
            if (!$module->defines_child_def) continue; // save a func call
            $return = $module->getChildDef($def);
            if ($return !== false) return $return;
        }
        // error-out
        trigger_error(
            'Could not determine which ChildDef class to instantiate',
            E_USER_ERROR
        );
        return false;
    }
    
    /**
     * Converts a string list of elements separated by pipes into
     * a lookup array.
     * @param $string List of elements
     * @return Lookup array of elements
     */
    function convertToLookup($string) {
        $array = explode('|', str_replace(' ', '', $string));
        $ret = array();
        foreach ($array as $i => $k) {
            $ret[$k] = true;
        }
        return $ret;
    }
    
}

?>
