<?php

require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/HTMLModuleManager.php';

// this definition and its modules MUST NOT define configuration directives
// outside of the HTML or Attr namespaces

HTMLPurifier_ConfigSchema::define(
    'HTML', 'BlockWrapper', 'p', 'string', '
<p>
    String name of element to wrap inline elements that are inside a block
    context.  This only occurs in the children of blockquote in strict mode.
</p>
<p>
    Example: by default value,
    <code>&lt;blockquote&gt;Foo&lt;/blockquote&gt;</code> would become
    <code>&lt;blockquote&gt;&lt;p&gt;Foo&lt;/p&gt;&lt;/blockquote&gt;</code>.
    The <code>&lt;p&gt;</code> tags can be replaced with whatever you desire,
    as long as it is a block level element. This directive has been available
    since 1.3.0.
</p>
');

HTMLPurifier_ConfigSchema::define(
    'HTML', 'Parent', 'div', 'string', '
<p>
    String name of element that HTML fragment passed to library will be 
    inserted in.  An interesting variation would be using span as the 
    parent element, meaning that only inline tags would be allowed. 
    This directive has been available since 1.3.0.
</p>
');

HTMLPurifier_ConfigSchema::define(
    'HTML', 'AllowedElements', null, 'lookup/null', '
<p>
    If HTML Purifier\'s tag set is unsatisfactory for your needs, you 
    can overload it with your own list of tags to allow.  Note that this 
    method is subtractive: it does its job by taking away from HTML Purifier 
    usual feature set, so you cannot add a tag that HTML Purifier never 
    supported in the first place (like embed, form or head).  If you 
    change this, you probably also want to change %HTML.AllowedAttributes. 
</p>
<p>
    <strong>Warning:</strong> If another directive conflicts with the 
    elements here, <em>that</em> directive will win and override. 
    This directive has been available since 1.3.0.
</p>
');

HTMLPurifier_ConfigSchema::define(
    'HTML', 'AllowedAttributes', null, 'lookup/null', '
<p>
    If HTML Purifier\'s attribute set is unsatisfactory, overload it! 
    The syntax is "tag.attr" or "*.attr" for the global attributes 
    (style, id, class, dir, lang, xml:lang).
</p>
<p>
    <strong>Warning:</strong> If another directive conflicts with the 
    elements here, <em>that</em> directive will win and override. For 
    example, %HTML.EnableAttrID will take precedence over *.id in this 
    directive.  You must set that directive to true before you can use 
    IDs at all. This directive has been available since 1.3.0.
</p>
');

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
class HTMLPurifier_HTMLDefinition extends HTMLPurifier_Definition
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
    
    /**
     * Doctype object
     */
    var $doctype;
    
    
    /** PUBLIC BUT INTERNAL VARIABLES */
    
    var $manager; /**< Instance of HTMLPurifier_HTMLModuleManager */
    
    /**
     * Performs low-cost, preliminary initialization.
     */
    function HTMLPurifier_HTMLDefinition() {
        $this->manager = new HTMLPurifier_HTMLModuleManager();
    }
    
    /**
     * Retrieve definition object from cache
     */
    function getCache($config) {
        static $cache = array();
        $file = HTMLPurifier_HTMLDefinition::getCacheFile($config);
        if (isset($cache[$file])) return $cache[$file]; // unit test optimization
        if (!file_exists($file)) return false;
        $cache[$file] = unserialize(file_get_contents($file));
        return $cache[$file];
    }
    
    /**
     * Determines a cache key identifier for a particular configuration
     */
    function getCacheKey($config) {
        return md5(serialize(array($config->getBatch('HTML'), $config->getBatch('Attr'))));
    }
    
    /**
     * Determines file a particular configuration's definition is stored in
     */
    function getCacheFile($config) {
        $key = HTMLPurifier_HTMLDefinition::getCacheKey($config);
        return dirname(__FILE__) . '/HTMLDefinition/' . $key . '.ser';
    }
    
    /**
     * Saves HTMLDefinition to cache
     */
    function saveCache($config) {
        $file = $this->getCacheFile($config);
        $contents = serialize($this);
        $fh = fopen($file, 'w');
        fwrite($fh, $contents);
        fclose($fh);
    }
    
    function doSetup($config) {
        $this->processModules($config);
        $this->setupConfigStuff($config);
        unset($this->manager);
    }
    
    /**
     * Extract out the information from the manager
     */
    function processModules($config) {
        
        $this->manager->setup($config);
        $this->doctype = $this->manager->doctype;
        
        foreach ($this->manager->modules as $module) {
            foreach($module->info_tag_transform         as $k => $v) {
                if ($v === false) unset($this->info_tag_transform[$k]);
                else $this->info_tag_transform[$k] = $v;
            }
            foreach($module->info_attr_transform_pre    as $k => $v) {
                if ($v === false) unset($this->info_attr_transform_pre[$k]);
                else $this->info_attr_transform_pre[$k] = $v;
            }
            foreach($module->info_attr_transform_post   as $k => $v) {
                if ($v === false) unset($this->info_attr_transform_post[$k]);
                else $this->info_attr_transform_post[$k] = $v;
            }
        }
        
        $this->info = $this->manager->getElements();
        $this->info_content_sets = $this->manager->contentSets->lookup;
        
    }
    
    /**
     * Sets up stuff based on config. We need a better way of doing this.
     */
    function setupConfigStuff($config) {
        
        $block_wrapper = $config->get('HTML', 'BlockWrapper');
        if (isset($this->info_content_sets['Block'][$block_wrapper])) {
            $this->info_block_wrapper = $block_wrapper;
        } else {
            trigger_error('Cannot use non-block element as block wrapper.',
                E_USER_ERROR);
        }
        
        $parent = $config->get('HTML', 'Parent');
        $def = $this->manager->getElement($parent, true);
        if ($def) {
            $this->info_parent = $parent;
            $this->info_parent_def = $def;
        } else {
            trigger_error('Cannot use unrecognized element as parent.',
                E_USER_ERROR);
            $this->info_parent_def = $this->manager->getElement($this->info_parent, true);
        }
        
        // support template text
        $support = "(for information on implementing this, see the ".
                   "support forums) ";
        
        // setup allowed elements, SubtractiveWhitelist module(?)
        $allowed_elements = $config->get('HTML', 'AllowedElements');
        if (is_array($allowed_elements)) {
            foreach ($this->info as $name => $d) {
                if(!isset($allowed_elements[$name])) unset($this->info[$name]);
                unset($allowed_elements[$name]);
            }
            // emit errors
            foreach ($allowed_elements as $element => $d) {
                $element = htmlspecialchars($element);
                trigger_error("Element '$element' is not supported $support", E_USER_WARNING);
            }
        }
        
        $allowed_attributes = $config->get('HTML', 'AllowedAttributes');
        $allowed_attributes_mutable = $allowed_attributes; // by copy!
        if (is_array($allowed_attributes)) {
            foreach ($this->info_global_attr as $attr_key => $info) {
                if (!isset($allowed_attributes["*.$attr_key"])) {
                    unset($this->info_global_attr[$attr_key]);
                } elseif (isset($allowed_attributes_mutable["*.$attr_key"])) {
                    unset($allowed_attributes_mutable["*.$attr_key"]);
                }
            }
            foreach ($this->info as $tag => $info) {
                foreach ($info->attr as $attr => $attr_info) {
                    if (!isset($allowed_attributes["$tag.$attr"]) &&
                        !isset($allowed_attributes["*.$attr"])) {
                        unset($this->info[$tag]->attr[$attr]);
                    } else {
                        if (isset($allowed_attributes_mutable["$tag.$attr"])) {
                            unset($allowed_attributes_mutable["$tag.$attr"]);
                        } elseif (isset($allowed_attributes_mutable["*.$attr"])) {
                            unset($allowed_attributes_mutable["*.$attr"]);
                        }
                    }
                }
            }
            // emit errors
            foreach ($allowed_attributes_mutable as $elattr => $d) {
                list($element, $attribute) = explode('.', $elattr);
                $element = htmlspecialchars($element);
                $attribute = htmlspecialchars($attribute);
                if ($element == '*') {
                    trigger_error("Global attribute '$attribute' is not ".
                        "supported in any elements $support",
                        E_USER_WARNING);
                } else {
                    trigger_error("Attribute '$attribute' in element '$element' not supported $support",
                        E_USER_WARNING);
                }
            }
        }
        
    }
    
    
}

?>
