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
     * Associative array of module class name to module order keywords or
     * numbers (keyword is preferred, all keywords are resolved at beginning
     * of setup())
     */
    var $order = array();
    
    /**
     * Associative array of module setup names to the corresponding safe
     * (as in no XSS, no full document markup) modules.
     */
    var $collectionsSafe = array(
        '_Common' => array( // leading _ indicates private
            'CommonAttributes',
            'Text',
            'Hypertext',
            'List',
            'Presentation',
            'Edit',
            'Bdo',
            'Tables',
            'Image',
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
     * Modules to import if lenient mode (attempt to convert everything
     * to a valid representation) is on
     */
    var $collectionsLenient = array(
        'HTML 4.01 Strict' => 'XHTML 1.0 Strict',
        'XHTML 1.0 Strict' => array('TransformToStrict'),
        'XHTML 1.1' => array(array('XHTML 1.0 Strict'), 'TransformToXHTML11')
    );
    
    /**
     * Modules to import if correctional mode (correct everything that
     * is feasible to strict mode) is on
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
                trigger_error($original_module . ' module does not exist', E_USER_ERROR);
                return;
            }
            $module = new $module();
        }
        $order = $module->type;
        if (!isset($this->orderKeywords[$order])) {
            trigger_error('Order keyword does not exist', E_USER_ERROR);
            return;
        }
        $this->modules[$module->name] = $module;
        $this->order[$module->name] = $order;
    }
    
    function setup($config) {
        // retrieve the doctype
        $this->doctype = $this->getDoctype($config);
        
        // substitute out the order keywords
        foreach ($this->order as $name => $order) {
            if (empty($this->modules[$name])) {
                trigger_error('Orphan module order definition for module: ' . $name, E_USER_ERROR);
                return;
            }
            if (is_int($order)) continue;
            if (empty($this->orderKeywords[$order])) {
                trigger_error('Unknown order keyword: ' . $order, E_USER_ERROR);
                return;
            }
            $this->order[$name] = $this->orderKeywords[$order];
        }
        
        // sort modules member variable
        array_multisort(
            $this->order, SORT_ASC, SORT_NUMERIC,
            $this->modules
        );
        
        // process module collections to module name => module instance form
        $this->processCollections($this->collectionsSafe);
        $this->processCollections($this->collectionsLenient);
        $this->processCollections($this->collectionsCorrectional);
        
        // setup the validModules array
        if (isset($this->collectionsSafe[$this->doctype])) {
            $this->validModules += $this->collectionsSafe[$this->doctype];
        }
        if (isset($this->collectionsLenient[$this->doctype])) {
            $this->validModules += $this->collectionsLenient[$this->doctype];
        }
        if (isset($this->collectionsCorrectional[$this->doctype])) {
            $this->validModules += $this->collectionsCorrectional[$this->doctype];
        }
        
        // setup the activeModules array
        $this->activeModules = $this->validModules; // unimplemented!
        
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
            $this->validModules
        );
        $this->attrCollections = new HTMLPurifier_AttrCollections(
            $this->attrTypes,
            $this->activeModules
        );
        
    }
    
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
            foreach ($col as $module_i => $module) {
                unset($cols[$col_i][$module_i]);
                $module = $this->modules[$module];
                $cols[$col_i][$module->name] = $module;
            }
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
    
    function getDoctype($config) {
        // get rid of this later
        if ($config->get('HTML', 'Strict')) {
            $doctype = 'XHTML 1.0 Strict';
        } else {
            $doctype = 'XHTML 1.0 Transitional';
        }
        return $doctype;
    }
    
    /**
     * @param $config
     */
    function getElements($config) {
        
        $elements = array();
        foreach ($this->activeModules as $module) {
            foreach ($module->elements as $name) {
                $elements[$name] = $this->getElement($name, $config);
            }
        }
        
        return $elements;
        
    }
    
    function getElement($name, $config) {
        
        $def = false;
        
        $modules = $this->validModules;
        
        if (!isset($this->elementModuleLookup[$name])) {
            return false;
        }
        
        foreach($this->elementModuleLookup[$name] as $module_name) {
            
            // oops, we can't use that module at all
            if (!isset($modules[$module_name])) continue;
            
            $module = $modules[$module_name];
            $new_def = $module->info[$name];
            
            if (!$def && $new_def->standalone) {
                $def = $new_def;
            } elseif ($def) {
                $def->mergeIn($new_def);
            } else {
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
    
    /**
     * Retrieves full child definition for child, for the parent. Parent
     * is a special case because it may not be allowed in the document.
     */
    function getFullChildDef($element, $config) {
        $def = $this->getElement($element, $config);
        if ($def === false) {
            trigger_error('Cannot get child def of element not available in doctype',
                E_USER_ERROR);
            return false;
        }
        return $def->child;
    }
    
}

?>