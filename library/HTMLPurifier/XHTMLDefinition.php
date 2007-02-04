<?php

require_once 'HTMLPurifier/HTMLDefinition.php';

require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrCollection.php';

require_once 'HTMLPurifier/HTMLModule.php';
require_once 'HTMLPurifier/HTMLModule/Text.php';
require_once 'HTMLPurifier/HTMLModule/Hypertext.php';
require_once 'HTMLPurifier/HTMLModule/List.php';
require_once 'HTMLPurifier/HTMLModule/Presentation.php';
require_once 'HTMLPurifier/HTMLModule/Edit.php';
require_once 'HTMLPurifier/HTMLModule/Bdo.php';

/**
 * Next-generation HTML definition that will supplant HTMLPurifier_HTMLDefinition
 */
class HTMLPurifier_XHTMLDefinition extends HTMLPurifier_HTMLDefinition
{
    
    var $modules = array();
    var $attr_types;
    var $attr_collection;
    var $content_sets;
    
    function HTMLPurifier_XHTMLDefinition($config) {
        
        $this->modules['Text']          = new HTMLPurifier_HTMLModule_Text();
        $this->modules['Hypertext']     = new HTMLPurifier_HTMLModule_Hypertext();
        $this->modules['List']          = new HTMLPurifier_HTMLModule_List();
        $this->modules['Presentation']  = new HTMLPurifier_HTMLModule_Presentation();
        $this->modules['Edit']          = new HTMLPurifier_HTMLModule_Edit();
        $this->modules['Bdo']           = new HTMLPurifier_HTMLModule_Bdo();
        
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
        // define convenient variables
        $content_sets_keys   = array_keys($content_sets);
        $content_sets_values = array_values($content_sets);
        foreach ($content_sets as $name => $set) {
            $this->content_sets[$name] = $this->convertToLookup($set);
        }
        
        foreach ($this->modules as $module_i => $module) {
            foreach ($module->info as $name => $def) {
                $def =& $this->modules[$module_i]->info[$name];
                
                // attribute value expansions
                $this->attr_collection->performInclusions($def->attr);
                $this->attr_collection->expandStringIdentifiers(
                    $def->attr, $this->attr_types);
                
                // perform content model expansions
                $content_model = $def->content_model;
                if (is_string($content_model)) {
                    if (strpos($content_model, 'Inline') !== false) {
                        $def->descendants_are_inline = true;
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
    
    function setupAttrTransform($config) {
        $this->info_attr_transform_post[] = new HTMLPurifier_AttrTransform_Lang();
    }
    
    function setupBlockWrapper($config) {
        $block_wrapper = $config->get('HTML', 'BlockWrapper');
        if (isset($this->content_sets['Block'][$block_wrapper])) {
            $this->info_block_wrapper = $block_wrapper;
        } else {
            trigger_error('Cannot use non-block element as block wrapper.',
                E_USER_ERROR);
        }
    }
    
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
    
    function getChildDef($def) {
        $value = $def->content_model;
        $type  = $def->content_model_type;
        switch ($type) {
            case 'required':
                return new HTMLPurifier_ChildDef_Required($value);
            case 'optional':
                return new HTMLPurifier_ChildDef_Optional($value);
            case 'empty':
                return new HTMLPurifier_ChildDef_Empty();
            case 'strictblockquote':
                return new HTMLPurifier_ChildDef_StrictBlockquote($value);
            case 'table':
                return new HTMLPurifier_ChildDef_Table();
            case 'chameleon':
                $value = explode('!', $value);
                return new HTMLPurifier_ChildDef_Chameleon($value[0], $value[1]);
            case 'custom':
                return new HTMLPurifier_ChildDef_Custom($value);
        }
        if ($value) return new HTMLPurifier_ChildDef_Optional($value);
        return HTMLPurifier_ChildDef_Empty();
    }
    
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
