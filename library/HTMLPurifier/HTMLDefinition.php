<?php

// components
require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrCollections.php';
require_once 'HTMLPurifier/ContentSets.php';
require_once 'HTMLPurifier/ElementDef.php';

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/AttrDef/Enum.php';

// temporary: attribute transformations
require_once 'HTMLPurifier/AttrTransform.php';
require_once 'HTMLPurifier/AttrTransform/Lang.php';
require_once 'HTMLPurifier/AttrTransform/TextAlign.php';
require_once 'HTMLPurifier/AttrTransform/BdoDir.php';
require_once 'HTMLPurifier/AttrTransform/ImgRequired.php';

// temporary: tag transformations
require_once 'HTMLPurifier/TagTransform.php';
require_once 'HTMLPurifier/TagTransform/Simple.php';
require_once 'HTMLPurifier/TagTransform/Center.php';
require_once 'HTMLPurifier/TagTransform/Font.php';

// default modules
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

// compat modules
require_once 'HTMLPurifier/HTMLModule/TransformToStrict.php';
require_once 'HTMLPurifier/HTMLModule/Legacy.php';

// config modules
require_once 'HTMLPurifier/HTMLModule/SetParent.php';

// tweak modules
require_once 'HTMLPurifier/HTMLModule/TweakSubtractiveWhitelist.php';

// this definition and its modules MUST NOT define configuration directives
// outside of the HTML or Attr namespaces

HTMLPurifier_ConfigSchema::define(
    'HTML', 'Strict', false, 'bool',
    'Determines whether or not to use Transitional (loose) or Strict rulesets. '.
    'This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'HTML', 'BlockWrapper', 'p', 'string',
    'String name of element to wrap inline elements that are inside a block '.
    'context.  This only occurs in the children of blockquote in strict mode. '.
    'Example: by default value, <code>&lt;blockquote&gt;Foo&lt;/blockquote&gt;</code> '.
    'would become <code>&lt;blockquote&gt;&lt;p&gt;Foo&lt;/p&gt;&lt;/blockquote&gt;</code>. The '.
    '<code>&lt;p&gt;</code> tags can be replaced '.
    'with whatever you desire, as long as it is a block level element. '.
    'This directive has been available since 1.3.0.'
);

/**
 * Definition of the purified HTML that describes allowed children,
 * attributes, and many other things.
 * 
 * Conventions:
 * 
 * All member variables that are prefixed with info
 * (including the main $info array) are used by HTML Purifier internals
 * and should not be directly edited when customizing the HTMLDefinition.
 * They can usually be set via configuration directives or custom
 * modules.
 * 
 * On the other hand, member variables without the info prefix are used
 * internally by the HTMLDefinition and MUST NOT be used by other HTML
 * Purifier internals. Many of them, however, are public, and may be
 * edited by userspace code to tweak the behavior of HTMLDefinition.
 * 
 * HTMLPurifier_Printer_HTMLDefinition is a notable exception to this
 * rule: in the interest of comprehensiveness, it will sniff everything.
 */
class HTMLPurifier_HTMLDefinition
{
    
    /** FULLY-PUBLIC VARIABLES */
    
    /**
     * Associative array of element names to HTMLPurifier_ElementDef
     * @public
     */
    var $info = array();
    
    /**
     * Associative array of global attribute name to attribute definition.
     * @public
     */
    var $info_global_attr = array();
    
    /**
     * String name of parent element HTML will be going into.
     * @public
     */
    var $info_parent = 'div';
    
    /**
     * Definition for parent element, allows parent element to be a
     * tag that's not allowed inside the HTML fragment.
     * @public
     */
    var $info_parent_def;
    
    /**
     * String name of element used to wrap inline elements in block context
     * @note This is rarely used except for BLOCKQUOTEs in strict mode
     * @public
     */
    var $info_block_wrapper = 'p';
    
    /**
     * Associative array of deprecated tag name to HTMLPurifier_TagTransform
     * @public
     */
    var $info_tag_transform = array();
    
    /**
     * Indexed list of HTMLPurifier_AttrTransform to be performed before validation.
     * @public
     */
    var $info_attr_transform_pre = array();
    
    /**
     * Indexed list of HTMLPurifier_AttrTransform to be performed after validation.
     * @public
     */
    var $info_attr_transform_post = array();
    
    /**
     * Nested lookup array of content set name (Block, Inline) to
     * element name to whether or not it belongs in that content set.
     * @public
     */
    var $info_content_sets = array();
    
    
    
    /** PUBLIC BUT INTERNAL VARIABLES */
    
    /**
     * Boolean is a strict definition?
     * @public
     */
    var $strict;
    
    /**
     * Array of HTMLPurifier_Module instances, indexed by module's class name
     * @public
     */
    var $modules = array();
    
    /**
     * Associative array of module class name to module order keywords or
     * numbers (keyword is preferred, all keywords are resolved at beginning
     * of setup())
     * @public
     */
    var $modules_order = array();
    
    /**
     * List of prefixes HTML Purifier should try to resolve short names to.
     * @public
     */
    var $module_prefixes = array('HTMLPurifier_HTMLModule_');
    
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
     * Has setup() been called yet?
     * @public
     */
    var $setup = false;
    
    /**
     * Instance of HTMLPurifier_ContentSets
     * @public
     */
    var $content_sets;
    
    /**
     * Lookup table of module order "names" and an integer index
     * @public
     */
    var $order_keywords = array(
        'begin'     => 10,
        'setup'     => 20,
        
        'pre'       => 30,
        
        'early'     => 40,
        'main'      => 50,
        'late'      => 60,
        
        'post'      => 70,
        
        'cleanup'   => 80,
        'end'       => 90
    );
    
    /**
     * Temporary instance of HTMLPurifier_Config for convenience reasons,
     * is removed after setup().
     * @public
     */
    var $config;
    
    
    /**
     * Performs low-cost, preliminary initialization.
     * @param $config Instance of HTMLPurifier_Config
     */
    function HTMLPurifier_HTMLDefinition(&$config) {
        
        $this->config =& $config;
        
        // set up public internals
        $this->strict           = $config->get('HTML', 'Strict');
        $this->attr_types       = new HTMLPurifier_AttrTypes();
        $this->attr_collections = new HTMLPurifier_AttrCollections();
        $this->content_sets     = new HTMLPurifier_ContentSets();
        
        // modules
        
        $main_modules = array('Text', 'Hypertext', 'List', 'Presentation',
            'Edit', 'Bdo', 'Tables', 'Image', 'StyleAttribute');
        foreach ($main_modules as $module) $this->addModule($module, 'main');
        
        if (!$this->strict) $this->addModule('Legacy', 'late');
        
        $this->addModule('SetParent', 'post');
        
        $this->addModule('TransformToStrict', 'cleanup');
        $this->addModule('TweakSubtractiveWhitelist', 'cleanup');
        
    }
    
    /**
     * Adds a module to the ordered list.
     * @param $module Mixed: string module name, with or without
     *                HTMLPurifier_HTMLModule prefix, or instance of
     *                subclass of HTMLPurifier_HTMLModule.
     */
    function addModule($module, $order = 'main') {
        if (is_string($module)) {
            $original_module = $module;
            if (!class_exists($module)) {
                foreach ($this->module_prefixes as $prefix) {
                    $module = $prefix . $original_module;
                    if (class_exists($module)) break;
                }
            }
            if (!class_exists($module)) {
                trigger_error($original_module . ' module does not exist', E_USER_ERROR);
                return;
            }
            $module = new $module($this);
        }
        if (!isset($this->order_keywords[$order])) {
            trigger_error('Order keyword does not exist', E_USER_ERROR);
            return;
        }
        $name = strtolower(get_class($module));
        $this->modules[$name] = $module;
        $this->modules_order[$name] = $order;
    }
    
    /**
     * Processes internals into form usable by HTMLPurifier internals. 
     * Modifying the definition after calling this function should not
     * be done.
     * @param $config Instance of HTMLPurifier_Config
     */
    function setup() {
        
        // multiple call guard
        if ($this->setup) {return;} else {$this->setup = true;}
        
        $this->processModules();
        $this->setupAttrTransform();
        $this->setupBlockWrapper();
        
        unset($this->config);
        
    }
    
    /**
     * Processes the modules, setting up related info variables
     */
    function processModules() {
        
        // substitute out the order keywords
        foreach ($this->modules_order as $name => $order) {
            if (empty($this->modules[$name])) {
                trigger_error('Orphan module order definition for module: ' . $name, E_USER_ERROR);
                return;
            }
            if (is_int($order)) continue;
            if (empty($this->order_keywords[$order])) {
                trigger_error('Unknown order keyword: ' . $order, E_USER_ERROR);
                return;
            }
            $this->modules_order[$name] = $this->order_keywords[$order];
        }
        
        // sort modules member variable
        array_multisort(
            $this->modules_order, SORT_ASC, SORT_NUMERIC,
            $this->modules
        );
        
        // setup the global registries
        $this->attr_collections->setup($this->attr_types, $this->modules);
        $this->content_sets->setup($this->modules);
        $this->info_content_sets = $this->content_sets->lookup;
        
        // process the modules
        foreach ($this->modules as $module_i => $module) {
            
            $module->preProcess($this);
            
            // process element-wise definitions
            foreach ($module->info as $name => $def) {
                // setup info
                if (!isset($this->info[$name])) {
                    if ($def->standalone) {
                        $this->info[$name] = $this->modules[$module_i]->info[$name];
                    } else {
                        // attempting to merge into an element that doesn't
                        // exist, ignore it
                        continue;
                    }
                } else {
                    $this->info[$name]->mergeIn($this->modules[$module_i]->info[$name]);
                }
                
                // process info
                $def = $this->info[$name];
                
                // attribute value expansions
                $this->attr_collections->performInclusions($def->attr);
                $this->attr_collections->expandIdentifiers(
                    $def->attr, $this->attr_types);
                
                // descendants_are_inline, for ChildDef_Chameleon
                if (is_string($def->content_model) &&
                strpos($def->content_model, 'Inline') !== false) {
                    if ($name != 'del' && $name != 'ins') {
                        // this is for you, ins/del
                        $def->descendants_are_inline = true;
                    }
                }
                
                // set child def from content model
                $this->content_sets->generateChildDef($def, $module);
                
                $this->info[$name] = $def;
                
            }
            
            // merge in global info variables from module
            foreach($module->info_tag_transform         as $k => $v) $this->info_tag_transform[$k]      = $v;
            foreach($module->info_attr_transform_pre    as $k => $v) $this->info_attr_transform_pre[$k] = $v;
            foreach($module->info_attr_transform_post   as $k => $v) $this->info_attr_transform_post[$k]= $v;
            
            $module->postProcess($this);
            
        }
        
    }
    
    /**
     * Sets up attribute transformations
     */
    function setupAttrTransform() {
        $this->info_attr_transform_post[] = new HTMLPurifier_AttrTransform_Lang();
    }
    
    /**
     * Sets up block wrapper based on config
     */
    function setupBlockWrapper() {
        $block_wrapper = $this->config->get('HTML', 'BlockWrapper');
        if (isset($this->info_content_sets['Block'][$block_wrapper])) {
            $this->info_block_wrapper = $block_wrapper;
        } else {
            trigger_error('Cannot use non-block element as block wrapper.',
                E_USER_ERROR);
        }
    }
    
    
}

?>
