<?php

// components
require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrCollections.php';
require_once 'HTMLPurifier/ContentSets.php';
require_once 'HTMLPurifier/ElementDef.php';

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/AttrDef/Enum.php'; // common

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

HTMLPurifier_ConfigSchema::define(
    'HTML', 'EnableAttrID', false, 'bool',
    'Allows the ID attribute in HTML.  This is disabled by default '.
    'due to the fact that without proper configuration user input can '.
    'easily break the validation of a webpage by specifying an ID that is '.
    'already on the surrounding HTML.  If you don\'t mind throwing caution to '.
    'the wind, enable this directive, but I strongly recommend you also '.
    'consider blacklisting IDs you use (%Attr.IDBlacklist) or prefixing all '.
    'user supplied IDs (%Attr.IDPrefix).  This directive has been available '.
    'since 1.2.0, and when set to true reverts to the behavior of pre-1.2.0 '.
    'versions.'
);

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

HTMLPurifier_ConfigSchema::define(
    'HTML', 'Parent', 'div', 'string',
    'String name of element that HTML fragment passed to library will be '.
    'inserted in.  An interesting variation would be using span as the '.
    'parent element, meaning that only inline tags would be allowed. '.
    'This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'HTML', 'AllowedElements', null, 'lookup/null',
    'If HTML Purifier\'s tag set is unsatisfactory for your needs, you '.
    'can overload it with your own list of tags to allow.  Note that this '.
    'method is subtractive: it does its job by taking away from HTML Purifier '.
    'usual feature set, so you cannot add a tag that HTML Purifier never '.
    'supported in the first place (like embed, form or head).  If you change this, you '.
    'probably also want to change %HTML.AllowedAttributes. '.
    '<strong>Warning:</strong> If another directive conflicts with the '.
    'elements here, <em>that</em> directive will win and override. '.
    'This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'HTML', 'AllowedAttributes', null, 'lookup/null',
    'IF HTML Purifier\'s attribute set is unsatisfactory, overload it! '.
    'The syntax is \'tag.attr\' or \'*.attr\' for the global attributes '.
    '(style, id, class, dir, lang, xml:lang).'.
    '<strong>Warning:</strong> If another directive conflicts with the '.
    'elements here, <em>that</em> directive will win and override. For '.
    'example, %HTML.EnableAttrID will take precedence over *.id in this '.
    'directive.  You must set that directive to true before you can use '.
    'IDs at all. This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'Attr', 'DisableURI', false, 'bool',
    'Disables all URIs in all forms. Not sure why you\'d want to do that '.
    '(after all, the Internet\'s founded on the notion of a hyperlink). '.
    'This directive has been available since 1.3.0.'
);

/**
 * Definition of the purified HTML that describes allowed children,
 * attributes, and many other things.
 * 
 * @note This is the next-gen definition that will be renamed to
 *       HTMLDefinition soon!
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
 * In practice, there will not be too many of them.
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
     * Is setup?
     * @public
     */
    var $setup = false;
    
    /**
     * Instance of HTMLPurifier_ContentSets
     * @public
     */
    var $content_sets;
    
    
    /**
     * Performs low-cost, preliminary initialization.
     * @param $config Instance of HTMLPurifier_Config
     */
    function HTMLPurifier_HTMLDefinition($config) {
        
        // setup some cached config variables
        // this will eventually influence module loading
        $this->strict = $config->get('HTML', 'Strict');
        
        // order is important!
        $this->modules['Text']          = new HTMLPurifier_HTMLModule_Text();
        $this->modules['Hypertext']     = new HTMLPurifier_HTMLModule_Hypertext();
        $this->modules['List']          = new HTMLPurifier_HTMLModule_List();
        $this->modules['Presentation']  = new HTMLPurifier_HTMLModule_Presentation();
        $this->modules['Edit']          = new HTMLPurifier_HTMLModule_Edit();
        $this->modules['Bdo']           = new HTMLPurifier_HTMLModule_Bdo();
        $this->modules['Tables']        = new HTMLPurifier_HTMLModule_Tables();
        $this->modules['Image']         = new HTMLPurifier_HTMLModule_Image();
        $this->modules['StyleAttribute']= new HTMLPurifier_HTMLModule_StyleAttribute();
        
        $this->modules['TransformToStrict'] = new HTMLPurifier_HTMLModule_TransformToStrict($config);
        
        $this->attr_types       = new HTMLPurifier_AttrTypes();
        $this->attr_collections = new HTMLPurifier_AttrCollections();
        $this->content_sets     = new HTMLPurifier_ContentSets();
        
        // some compat stuff, will be factored to modules
        
        // remove ID module
        if (!$config->get('HTML', 'EnableAttrID')) {
            $this->attr_collections->info['Core']['id'] = false;
        }
        
    }
    
    
    
    /**
     * Processes internals into form usable by HTMLPurifier internals. 
     * Modifying the definition after calling this function should not
     * be done.
     * @param $config Instance of HTMLPurifier_Config
     */
    function setup($config) {
        
        // multiple call guard
        if ($this->setup) {return;} else {$this->setup = true;}
        
        // would be nice if we could put each of these in their
        // own object, would make this hookable too!
        $this->processModules($config);
        $this->setupAttrTransform($config);
        $this->setupBlockWrapper($config);
        $this->setupParent($config);
        $this->setupCompat($config);
        
    }
    
    /**
     * Processes the modules, setting up related info variables
     * @param $config Instance of HTMLPurifier_Config
     */
    function processModules($config) {
        $this->attr_collections->setup($this->attr_types, $this->modules);
        $this->content_sets->setup($this->modules);
        $this->info_content_sets = $this->content_sets->lookup;
        
        foreach ($this->modules as $module_i => $module) {
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
        }
        
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
     * Sets up compat code from HTMLDefinition that has not been
     * delegated to modules yet
     */
    function setupCompat($config) {
        
        // convenience for compat
        $e_Inline = new HTMLPurifier_ChildDef_Optional(
                        $this->info_content_sets['Inline'] +
                        array('#PCDATA' => true));
        
        // blockquote alt child def, implement in Legacy
        if (!$this->strict) {
            $this->info['blockquote']->child =
                new HTMLPurifier_ChildDef_Optional(
                    $this->info_content_sets['Flow'] +
                    array('#PCDATA' => true));
        }
        
        // deprecated element definitions, implement in Legacy
        if (!$this->strict) {
            $this->info['u'] =
            $this->info['s'] =
            $this->info['strike'] = new HTMLPurifier_ElementDef();
            $this->info['u']->child =
            $this->info['s']->child =
            $this->info['strike']->child = $e_Inline;
            $this->info['u']->descendants_are_inline =
            $this->info['s']->descendants_are_inline =
            $this->info['strike']->descendants_are_inline = true;
        }
        
        // changed content model for loose, implement in Legacy
        if ($this->strict) {
            $this->info['address']->child = $e_Inline;
        } else {
            $this->info['address']->child =
                new HTMLPurifier_ChildDef_Optional(
                $this->info_content_sets['Inline'] + 
                array('#PCDATA' => true, 'p' => true));
        }
        
        // deprecated config setting, implement in DisableURI module
        if ($config->get('Attr', 'DisableURI')) {
            $this->info['a']->attr['href'] =
            $this->info['img']->attr['longdesc'] =
            $this->info['del']->attr['cite'] =
            $this->info['ins']->attr['cite'] =
            $this->info['blockquote']->attr['cite'] =
            $this->info['q']->attr['cite'] = 
            $this->info['img']->attr['src'] = null;
        }
        
        // deprecated attributes implementations, implement in Legacy
        if (!$this->strict) {
            $this->info['li']->attr['value'] = new HTMLPurifier_AttrDef_Integer();
            $this->info['ol']->attr['start'] = new HTMLPurifier_AttrDef_Integer();
        }
        
        // setup allowed elements, SubtractiveWhitelist module
        $allowed_elements = $config->get('HTML', 'AllowedElements');
        if (is_array($allowed_elements)) {
            foreach ($this->info as $name => $d) {
                if(!isset($allowed_elements[$name])) unset($this->info[$name]);
            }
        }
        $allowed_attributes = $config->get('HTML', 'AllowedAttributes');
        if (is_array($allowed_attributes)) {
            foreach ($this->info_global_attr as $attr_key => $info) {
                if (!isset($allowed_attributes["*.$attr_key"])) {
                    unset($this->info_global_attr[$attr_key]);
                }
            }
            foreach ($this->info as $tag => $info) {
                foreach ($info->attr as $attr => $attr_info) {
                    if (!isset($allowed_attributes["$tag.$attr"])) {
                        unset($this->info[$tag]->attr[$attr]);
                    }
                }
            }
        }
        
    }
    
}

?>
