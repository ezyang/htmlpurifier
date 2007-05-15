<?php

require_once 'HTMLPurifier/HTMLModule.php';
require_once 'HTMLPurifier/ElementDef.php';
require_once 'HTMLPurifier/Doctype.php';
require_once 'HTMLPurifier/DoctypeRegistry.php';

require_once 'HTMLPurifier/ContentSets.php';
require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrCollections.php';

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/AttrDef/Enum.php';

// W3C modules
require_once 'HTMLPurifier/HTMLModule/CommonAttributes.php';
require_once 'HTMLPurifier/HTMLModule/Text.php';
require_once 'HTMLPurifier/HTMLModule/Hypertext.php';
require_once 'HTMLPurifier/HTMLModule/List.php';
require_once 'HTMLPurifier/HTMLModule/Presentation.php';
require_once 'HTMLPurifier/HTMLModule/Edit.php';
require_once 'HTMLPurifier/HTMLModule/Bdo.php';
require_once 'HTMLPurifier/HTMLModule/Tables.php';
require_once 'HTMLPurifier/HTMLModule/Image.php';
require_once 'HTMLPurifier/HTMLModule/StyleAttribute.php';
require_once 'HTMLPurifier/HTMLModule/Legacy.php';
require_once 'HTMLPurifier/HTMLModule/Target.php';
require_once 'HTMLPurifier/HTMLModule/Scripting.php';

// proprietary modules
require_once 'HTMLPurifier/HTMLModule/TransformToStrict.php';
require_once 'HTMLPurifier/HTMLModule/TransformToXHTML11.php';

HTMLPurifier_ConfigSchema::define(
    'HTML', 'Doctype', null, 'string/null',
    'Doctype to use, pre-defined values are HTML 4.01 Transitional, HTML 4.01 '.
    'Strict, XHTML 1.0 Transitional, XHTML 1.0 Strict, XHTML 1.1. '.
    'Technically speaking this is not actually a doctype (as it does '.
    'not identify a corresponding DTD), but we are using this name '.
    'for sake of simplicity. This will override any older directives '.
    'like %Core.XHTML or %HTML.Strict.'
);

class HTMLPurifier_HTMLModuleManager
{
    
    /**
     * Instance of HTMLPurifier_DoctypeRegistry
     * @public
     */
    var $doctypes;
    
    /**
     * Instance of HTMLPurifier_AttrTypes
     * @public
     */
    var $attrTypes;
    
    /**
     * Active instances of modules for the specified doctype are
     * indexed, by name, in this array.
     */
    var $modules = array();
    
    /**
     * Array of recognized HTMLPurifier_Module instances, indexed by 
     * module's class name. This array is usually lazy loaded, but a
     * user can overload a module by pre-emptively registering it.
     */
    var $registeredModules = array();
    
    /**
     * Associative array of element name to list of modules that have
     * definitions for the element; this array is dynamically filled.
     */
    var $elementLookup = array();
    
    /** List of prefixes we should use for registering small names */
    var $prefixes = array('HTMLPurifier_HTMLModule_');
    
    var $contentSets;     /**< Instance of HTMLPurifier_ContentSets */
    var $attrCollections; /**< Instance of HTMLPurifier_AttrCollections */
    
    /** If set to true, unsafe elements and attributes will be allowed */
    var $trusted = false;
    
    function HTMLPurifier_HTMLModuleManager() {
        
        // editable internal objects
        $this->attrTypes = new HTMLPurifier_AttrTypes();
        $this->doctypes  = new HTMLPurifier_DoctypeRegistry();
        
        // setup default HTML doctypes
        
        // module reuse
        $common = array(
            'CommonAttributes', 'Text', 'Hypertext', 'List',
            'Presentation', 'Edit', 'Bdo', 'Tables', 'Image',
            'StyleAttribute', 'Scripting'
        );
        $transitional = array('Legacy', 'Target');
        
        $this->doctypes->register(
            'HTML 4.01 Transitional',
            array_merge($common, $transitional),
            array('correctional' => array('TransformToStrict'))
        );
        
        $this->doctypes->register(
            'XHTML 1.0 Transitional',
            array_merge($common, $transitional),
            array('correctional' => array('TransformToStrict'))
        );
        
        $this->doctypes->register(
            'HTML 4.01 Strict',
            array_merge($common),
            array('lenient' => array('TransformToStrict'))
        );
        
        $this->doctypes->register(
            'XHTML 1.0 Strict',
            array_merge($common),
            array('lenient' => array('TransformToStrict'))
        );
        
        $this->doctypes->register(
            'XHTML 1.1',
            array_merge($common),
            array('lenient' => array('TransformToStrict', 'TransformToXHTML11'))
        );
        
    }
    
    /**
     * Registers a module to the recognized module list, useful for
     * overloading pre-existing modules.
     * @param $module Mixed: string module name, with or without
     *                HTMLPurifier_HTMLModule prefix, or instance of
     *                subclass of HTMLPurifier_HTMLModule.
     * @note This function will not call autoload, you must instantiate
     *       (and thus invoke) autoload outside the method.
     * @note If a string is passed as a module name, different variants
     *       will be tested in this order:
     *          - Check for HTMLPurifier_HTMLModule_$name
     *          - Check all prefixes with $name in order they were added
     *          - Check for literal object name
     *          - Throw fatal error
     *       If your object name collides with an internal class, specify
     *       your module manually. All modules must have been included
     *       externally: registerModule will not perform inclusions for you!
     * @warning If your module has the same name as an already loaded
     *          module, your module will overload the old one WITHOUT
     *          warning.
     */
    function registerModule($module) {
        if (is_string($module)) {
            // attempt to load the module
            $original_module = $module;
            $ok = false;
            foreach ($this->prefixes as $prefix) {
                $module = $prefix . $original_module;
                if ($this->_classExists($module)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                $module = $original_module;
                if (!$this->_classExists($module)) {
                    trigger_error($original_module . ' module does not exist',
                        E_USER_ERROR);
                    return;
                }
            }
            $module = new $module();
        }
        $this->registeredModules[$module->name] = $module;
    }
    
    /**
     * Safely tests for class existence without invoking __autoload in PHP5
     * or greater.
     * @param $name String class name to test
     * @note If any other class needs it, we'll need to stash in a 
     *       conjectured "compatibility" class
     * @private
     */
    function _classExists($name) {
        static $is_php_4 = null;
        if ($is_php_4 === null) {
            $is_php_4 = version_compare(PHP_VERSION, '5', '<');
        }
        if ($is_php_4) {
            return class_exists($name);
        } else {
            return class_exists($name, false);
        }
    }
    
    /**
     * Adds a module to the current doctype by first registering it,
     * and then tacking it on to the active doctype
     */
    function addModule($module) {
        // unimplemented
    }
    
    /**
     * Adds a class prefix that registerModule() will use to resolve a
     * string name to a concrete class
     */
    function addPrefix($prefix) {
        $this->prefixes[] = $prefix;
    }
    
    /**
     * Performs processing on modules, after being called you may
     * use getElement() and getElements()
     * @param $config Instance of HTMLPurifier_Config
     */
    function setup($config) {
        
        // generate
        $doctype = $this->doctypes->make($config);
        $modules = $doctype->modules;
        
        foreach ($modules as $module) {
            if (is_object($module)) {
                $this->modules[$module->name] = $module;
                continue;
            } else {
                if (!isset($this->modules[$module])) {
                    $this->registerModule($module);
                }
                $this->modules[$module] = $this->registeredModules[$module]; 
            }
        }
        
        // setup lookup table based on all valid modules
        foreach ($this->modules as $module) {
            foreach ($module->info as $name => $def) {
                if (!isset($this->elementLookup[$name])) {
                    $this->elementLookup[$name] = array();
                }
                $this->elementLookup[$name][] = $module->name;
            }
        }
        
        // note the different choice
        $this->contentSets = new HTMLPurifier_ContentSets(
            // content set assembly deals with all possible modules,
            // not just ones deemed to be "safe"
            $this->modules
        );
        $this->attrCollections = new HTMLPurifier_AttrCollections(
            $this->attrTypes,
            // there is no way to directly disable a global attribute,
            // but using AllowedAttributes or simply not including
            // the module in your custom doctype should be sufficient
            $this->modules
        );
        
    }
    
    /**
     * Retrieves merged element definitions.
     * @param $config Instance of HTMLPurifier_Config, for determining
     *                stray elements.
     * @return Array of HTMLPurifier_ElementDef
     */
    function getElements($config) {
        
        $elements = array();
        foreach ($this->modules as $module) {
            foreach ($module->info as $name => $v) {
                if (isset($elements[$name])) continue;
                // if element is not safe, don't use it
                if (!$this->trusted && ($v->safe === false)) continue;
                $elements[$name] = $this->getElement($name, $config);
            }
        }
        
        // remove dud elements, this happens when an element that
        // appeared to be safe actually wasn't
        foreach ($elements as $n => $v) {
            if ($v === false) unset($elements[$n]);
        }
        
        return $elements;
        
    }
    
    /**
     * Retrieves a single merged element definition
     * @param $name Name of element
     * @param $config Instance of HTMLPurifier_Config, may not be necessary.
     * @param $trusted Boolean trusted overriding parameter: set to true
     *                 if you want the full version of an element
     * @return Merged HTMLPurifier_ElementDef
     */
    function getElement($name, $config, $trusted = null) {
        
        $def = false;
        if ($trusted === null) $trusted = $this->trusted;
        
        $modules = $this->modules;
        
        if (!isset($this->elementLookup[$name])) {
            return false;
        }
        
        foreach($this->elementLookup[$name] as $module_name) {
            
            $module = $modules[$module_name];
            $new_def = $module->info[$name];
            
            // refuse to create/merge in a definition that is deemed unsafe
            if (!$trusted && ($new_def->safe === false)) {
                $def = false;
                continue;
            }
            
            if (!$def && $new_def->standalone) {
                // element with unknown safety is not to be trusted.
                // however, a merge-in definition with undefined safety
                // is fine
                if (!$new_def->safe) continue;
                $def = $new_def;
            } elseif ($def) {
                $def->mergeIn($new_def);
            } else {
                // could "save it for another day":
                // non-standalone definitions that don't have a standalone
                // to merge into could be deferred to the end
                continue;
            }
            
            // attribute value expansions
            $this->attrCollections->performInclusions($def->attr);
            $this->attrCollections->expandIdentifiers($def->attr, $this->attrTypes);
            
            // descendants_are_inline, for ChildDef_Chameleon
            if (is_string($def->content_model) &&
                strpos($def->content_model, 'Inline') !== false) {
                if ($name != 'del' && $name != 'ins') {
                    // this is for you, ins/del
                    $def->descendants_are_inline = true;
                }
            }
            
            $this->contentSets->generateChildDef($def, $module);
        }
        
        return $def;
        
    }
    
}

?>
