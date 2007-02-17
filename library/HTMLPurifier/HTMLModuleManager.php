<?php

require_once 'HTMLPurifier/ContentSets.php';
require_once 'HTMLPurifier/HTMLModule.php';

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

// proprietary modules
require_once 'HTMLPurifier/HTMLModule/TransformToStrict.php';
require_once 'HTMLPurifier/HTMLModule/TransformToXHTML11.php';

class HTMLPurifier_HTMLModuleManager
{
    
    /**
     * Array of HTMLPurifier_Module instances, indexed by module's class name.
     * All known modules, regardless of use, are in this array.
     */
    var $modules = array();
    
    /**
     * Modules that may be used in a valid doctype of this kind.
     * Correctional and leniency modules should not be placed in this
     * array unless the user said so: don't stuff every possible lenient
     * module for this doctype in here.
     */
    var $validModules = array();
    
    /**
     * Modules that we will use broadly, subset of validModules. Single
     * element definitions may result in us consulting validModules.
     */
    var $activeModules = array();
    
    /**
     * Current doctype for which $validModules is based
     */
    var $doctype;
    
    /**
     * Designates next available integer order for modules.
     */
    var $moduleCounter = 0;
    
    /**
     * List of suffixes of collections to process
     */
    var $collections = array('Safe', 'Unsafe', 'Lenient', 'Correctional');
    
    /**
     * Associative array of module setup names to the corresponding safe
     * (as in no XSS, no full document markup) modules. These are
     * included in both valid and active module lists by default.
     */
    var $collectionsSafe = array(
        '_Common' => array( // leading _ indicates private
            'CommonAttributes', 'Text', 'Hypertext', 'List',
            'Presentation', 'Edit', 'Bdo', 'Tables', 'Image',
            'StyleAttribute'
        ),
        // HTML definitions, defer completely to XHTML definitions
        'HTML 4.01 Transitional' => 'XHTML 1.0 Transitional',
        'HTML 4.01 Strict' => 'XHTML 1.0 Strict',
        // XHTML definitions
        'XHTML 1.0 Transitional' => array( array('XHTML 1.0 Strict'), 'Legacy' ),
        'XHTML 1.0 Strict' => array(array('_Common')),
        'XHTML 1.1' => array(array('_Common')),
    );
    
    /**
     * Modules that specify elements that are unsafe from untrusted
     * third-parties. These should be registered in $validModules but
     * almost never $activeModules unless you really know what you're
     * doing.
     */
    var $collectionsUnsafe = array( );
    
    /**
     * Modules to import if lenient mode (attempt to convert everything
     * to a valid representation) is on. These must not be in $activeModules
     * unless specified so.
     */
    var $collectionsLenient = array(
        'HTML 4.01 Strict' => 'XHTML 1.0 Strict',
        'XHTML 1.0 Strict' => array('TransformToStrict'),
        'XHTML 1.1' => array(array('XHTML 1.0 Strict'), 'TransformToXHTML11')
    );
    
    /**
     * Modules to import if correctional mode (correct everything that
     * is feasible to strict mode) is on. These must not be in $activeModules
     * unless specified so.
     */
    var $collectionsCorrectional = array(
        'HTML 4.01 Transitional' => 'XHTML 1.0 Transitional',
        'XHTML 1.0 Transitional' => array('TransformToStrict'), // probably want a different one
    );
    
    /** Associative array of element name to defining modules (always array) */
    var $elementModuleLookup = array();
    
    /** List of prefixes we should use for resolving small names */
    var $prefixes = array('HTMLPurifier_HTMLModule_');
    
    /** Associative array of order keywords to an integer index */
    var $orderKeywords = array(
        'define' => 10,
        'define-redefine' => 20,
        'redefine' => 30,
    );
    
    /** Instance of HTMLPurifier_ContentSets configured with full modules. */
    var $contentSets;
    
    var $attrTypes; /**< Instance of HTMLPurifier_AttrTypes */
    var $attrCollections; /**< Instance of HTMLPurifier_AttrCollections */
    
    function HTMLPurifier_HTMLModuleManager() {
        
        // modules
        $modules = array(
            // define
            'CommonAttributes',
            'Text', 'Hypertext', 'List', 'Presentation',
            'Edit', 'Bdo', 'Tables', 'Image', 'StyleAttribute',
            // define-redefine
            'Legacy',
            // redefine
            'TransformToStrict', 'TransformToXHTML11'
        );
        
        foreach ($modules as $module) {
            $this->addModule($module);
        }
        
        // the only editable internal object. The rest need to
        // be manipulated through modules
        $this->attrTypes = new HTMLPurifier_AttrTypes();
        
    }
    
    /**
     * Adds a module to the ordered list.
     * @param $module Mixed: string module name, with or without
     *                HTMLPurifier_HTMLModule prefix, or instance of
     *                subclass of HTMLPurifier_HTMLModule.
     */
    function addModule($module) {
        if (is_string($module)) {
            $original_module = $module;
            if (!class_exists($module)) {
                foreach ($this->prefixes as $prefix) {
                    $module = $prefix . $original_module;
                    if (class_exists($module)) break;
                }
            }
            if (!class_exists($module)) {
                trigger_error($original_module . ' module does not exist',
                    E_USER_ERROR);
                return;
            }
            $module = new $module();
        }
        $module->order = $this->moduleCounter++; // assign then increment
        $this->modules[$module->name] = $module;
    }
    
    function setup($config) {
        // retrieve the doctype
        $this->doctype = $this->getDoctype($config);
        
        // process module collections to module name => module instance form
        foreach ($this->collections as $suffix) {
            $varname = 'collections' . $suffix;
            $this->processCollections($this->$varname);
        }
        
        // $collections variable in following instances will be dynamically
        // generated once we figure out some config variables
        
        // setup the validModules array
        $collections = array('Safe', 'Unsafe', 'Lenient', 'Correctional');
        $this->validModules = $this->assembleModules($collections);
        
        // setup the activeModules array
        $collections = array('Safe', 'Lenient', 'Correctional');
        $this->activeModules = $this->assembleModules($collections);
        
        // setup lookup table based on all valid modules
        foreach ($this->validModules as $module) {
            foreach ($module->elements as $name) {
                if (!isset($this->elementModuleLookup[$name])) {
                    $this->elementModuleLookup[$name] = array();
                }
                $this->elementModuleLookup[$name][] = $module->name;
            }
        }
        
        // note the different choice
        $this->contentSets = new HTMLPurifier_ContentSets(
            // content models that contain non-allowed elements are 
            // harmless because RemoveForeignElements will ensure
            // they never get in anyway, and there is usually no
            // reason why you should want to restrict a content
            // model beyond what is mandated by the doctype.
            // Note, however, that this means redefinitions of
            // content models can't be tossed in validModels willy-nilly:
            // that stuff still is regulated by configuration.
            $this->validModules
        );
        $this->attrCollections = new HTMLPurifier_AttrCollections(
            $this->attrTypes,
            // only explicitly allowed modules are allowed to affect
            // the global attribute collections. This mean's there's
            // a distinction between loading the Bdo module, and the
            // bdo element: Bdo will enable the dir attribute on all
            // elements, while bdo will only define the bdo element,
            // which will not have an editable directionality. This might
            // catch people who are loading only elements by surprise, so
            // we should consider loading an entire module if all the
            // elements it defines are requested by the user, especially
            // if it affects the global attribute collections.
            $this->activeModules
        );
        
    }
    
    /**
     * Takes a list of collections and merges together all the defined
     * modules for the current doctype from those collections.
     * @param $collections List of collection suffixes we should grab
     *                     modules from (like 'Safe' or 'Lenient')
     */
    function assembleModules($collections) {
        $modules = array();
        foreach ($collections as $suffix) {
            $varname = 'collections' . $suffix;
            $cols = $this->$varname;
            if (!empty($cols[$this->doctype])) {
                $modules += $cols[$this->doctype];
            }
        }
        return $modules;
    }
    
    /**
     * Takes a collection and performs inclusions and substitutions for it.
     * @param $cols Reference to collections class member variable
     */
    function processCollections(&$cols) {
        
        // $cols is the set of collections
        // $col_i is the name (index) of a collection
        // $col is a collection/list of modules
        
        // perform inclusions
        foreach ($cols as $col_i => $col) {
            if (is_string($col)) continue; // alias, save for later
            if (!is_array($col[0])) continue; // no inclusions to do
            $includes = $col[0];
            unset($cols[$col_i][0]); // remove inclusions value
            for ($i = 0; isset($includes[$i]); $i++) {
                $inc = $includes[$i];
                foreach ($cols[$inc] as $module) {
                    if (is_array($module)) { // another inclusion!
                        foreach ($module as $inc2) $includes[] = $inc2;
                        continue;
                    }
                    $cols[$col_i][] = $module; // merge in the other modules
                }
            }
        }
        
        // replace with real modules, invert module from list to
        // assoc array of module name to module instance
        foreach ($cols as $col_i => $col) {
            if (is_string($col)) continue;
            $order = array();
            foreach ($col as $module_i => $module) {
                unset($cols[$col_i][$module_i]);
                $module = $this->modules[$module];
                $cols[$col_i][$module->name] = $module;
                $order[$module->name] = $module->order;
            }
            array_multisort(
                $order, SORT_ASC, SORT_NUMERIC, $cols[$col_i]
            );
        }
        
        // hook up aliases
        foreach ($cols as $col_i => $col) {
            if (!is_string($col)) continue;
            $cols[$col_i] = $cols[$col];
        }
        
        // delete pseudo-collections
        foreach ($cols as $col_i => $col) {
            if ($col_i[0] == '_') unset($cols[$col_i]);
        }
        
    }
    
    /**
     * Retrieves the doctype from the configuration object
     */
    function getDoctype($config) {
        if ($config->get('Core', 'XHTML')) {
            $doctype = 'XHTML 1.0';
        } else {
            $doctype = 'HTML 4.01';
        }
        if ($config->get('HTML', 'Strict')) {
            $doctype .= ' Strict';
        } else {
            $doctype .= ' Transitional';
        }
        return $doctype;
    }
    
    /**
     * Retrieves merged element definitions for all active elements.
     * @note We may want to generate an elements array during setup
     *       and pass that on, because a specific combination of
     *       elements may trigger the loading of a module.
     * @param $config Instance of HTMLPurifier_Config, for determining
     *                stray elements.
     */
    function getElements($config) {
        
        $elements = array();
        foreach ($this->activeModules as $module) {
            foreach ($module->elements as $name) {
                $elements[$name] = $this->getElement($name, $config);
            }
        }
        
        // standalone elements now loaded
        
        return $elements;
        
    }
    
    /**
     * Retrieves a single merged element definition
     * @param $name Name of element
     * @param $config Instance of HTMLPurifier_Config, may not be necessary.
     */
    function getElement($name, $config) {
        
        $def = false;
        
        $modules = $this->validModules;
        
        if (!isset($this->elementModuleLookup[$name])) {
            return false;
        }
        
        foreach($this->elementModuleLookup[$name] as $module_name) {
            
            $module = $modules[$module_name];
            $new_def = $module->info[$name];
            
            if (!$def && $new_def->standalone) {
                $def = $new_def;
            } elseif ($def) {
                $def->mergeIn($new_def);
            } else {
                // could have save it for another day functionality:
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