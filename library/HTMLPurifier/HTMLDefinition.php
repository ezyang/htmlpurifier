<?php

require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrCollections.php';
require_once 'HTMLPurifier/ElementDef.php';

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

// handling attribute transformations until modules gain the capability
require_once 'HTMLPurifier/AttrTransform.php';
require_once 'HTMLPurifier/AttrTransform/Lang.php';
require_once 'HTMLPurifier/AttrTransform/TextAlign.php';
require_once 'HTMLPurifier/AttrTransform/BdoDir.php';
require_once 'HTMLPurifier/AttrTransform/ImgRequired.php';

// handling tag transformations until modules gain the capability
require_once 'HTMLPurifier/TagTransform.php';

// utility classes that are necessary (?)
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Token.php';

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
     * List of HTMLPurifier_AttrTransform to be performed before validation.
     * @public
     */
    var $info_attr_transform_pre = array();
    
    /**
     * List of HTMLPurifier_AttrTransform to be performed after validation.
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
     * Performs low-cost, preliminary initialization.
     * @param $config Instance of HTMLPurifier_Config
     */
    function HTMLPurifier_HTMLDefinition($config) {
        
        // setup some cached config variables
        // this will eventually influence module loading
        $this->strict = $config->get('HTML', 'Strict');
        
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
        if ($this->setup) return;
        $this->setup = true;
        
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
        $this->setupCompat($config);
        
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
        
        $e_Inline = new HTMLPurifier_ChildDef_Optional(
                        $this->info_content_sets['Inline'] +
                        array('#PCDATA' => true));
        
        // blockquote changes, implement in TransformStrict and Legacy
        if ($this->strict) {
            $this->info['blockquote']->child =
                new HTMLPurifier_ChildDef_StrictBlockquote(
                    $this->info_content_sets['Block'] +
                    array('#PCDATA' => true));
        } else {
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
        
        // custom, not sure where to implement, because it's not
        // just /one/ module
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
        
        // deprecated elements transforms, implement in TransformToStrict
        $this->info_tag_transform['font']   = new HTMLPurifier_TagTransform_Font();
        $this->info_tag_transform['menu']   = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['dir']    = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['center'] = new HTMLPurifier_TagTransform_Center();
        
        // deprecated attribute transforms, implement in TransformToStrict
        $this->info['h1']->attr_transform_pre[] =
        $this->info['h2']->attr_transform_pre[] =
        $this->info['h3']->attr_transform_pre[] =
        $this->info['h4']->attr_transform_pre[] =
        $this->info['h5']->attr_transform_pre[] =
        $this->info['h6']->attr_transform_pre[] =
        $this->info['p'] ->attr_transform_pre[] = 
                    new HTMLPurifier_AttrTransform_TextAlign();
        
        // xml:lang <=> lang mirroring, implement in TransformToStrict?
        $this->info_attr_transform_post[] = new HTMLPurifier_AttrTransform_Lang();
        $this->info_global_attr['lang'] = new HTMLPurifier_AttrDef_Lang();
        
        // setup allowed elements, obsoleted by Modules? (does offer
        // different functionality)
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
