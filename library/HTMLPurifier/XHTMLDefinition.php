<?php

require_once 'HTMLPurifier/HTMLDefinition.php';

require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrCollection.php';
require_once 'HTMLPurifier/HTMLModule.php';
require_once 'HTMLPurifier/HTMLModule/Text.php';

/**
 * Next-generation HTML definition that will supplant HTMLPurifier_HTMLDefinition
 */
class HTMLPurifier_XHTMLDefinition extends HTMLPurifier_HTMLDefinition
{
    
    var $modules = array();
    var $attr_types;
    var $attr_collection;
    
    function initialize($config) {
        
        $this->modules['Text'] = new HTMLPurifier_HTMLModule_Text();
        
        $this->attr_types = new HTMLPurifier_AttrTypes();
        $this->attr_collection = new HTMLPurifier_AttrCollection();
        
    }
    
    function setup($config) {
        
        // perform attribute collection substitutions
        $this->attr_collection->setup($this->attr_types, $this->modules);
        
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
        $content_sets_keys   = array_keys($content_sets);
        $content_sets_values = array_values($content_sets);
        
        foreach ($this->modules as $module_i => $module) {
            foreach ($module->info as $element_i => $element) {
                $element =& $this->modules[$module_i]->info[$element_i];
                
                // attribute value expansions
                $this->attr_collection->performInclusions($element->attr);
                
                // perform content model expansions
                $content_model = $element->content_model;
                if (is_string($content_model)) {
                    $element->content_model = str_replace(
                        $content_sets_keys, $content_sets_values, $content_model);
                }
                
                // setup info
                $this->info[$element_i] = new HTMLPurifier_ElementDef();
                $this->info[$element_i]->attr = $element->attr;
                $this->info[$element_i]->child = $this->getChildDef($element);
                if ($this->info_parent == $element_i) {
                    $this->info_parent_def = $this->info[$element_i];
                }
                
            }
        }
        
    }
    
    function getChildDef($element) {
        $value = $element->content_model;
        $type  = $element->content_model_type;
        switch ($type) {
            case 'required':
                return new HTMLPurifier_ChildDef_Required($value);
            case 'optional':
                return new HTMLPurifier_ChildDef_Optional($value);
            case 'empty':
                return new HTMLPurifier_ChildDef_Empty();
            case 'strictblockquote':
                return new HTMLPurifier_ChildDef_StrictBlockquote();
            case 'table':
                return new HTMLPurifier_ChildDef_Table();
            case 'chameleon':
                return new HTMLPurifier_ChildDef_Chameleon($value[0], $value[1]);
            case 'custom':
                return new HTMLPurifier_ChildDef_Custom($value);
        }
        return HTMLPurifier_ChildDef_Empty();
    }
    
}

?>