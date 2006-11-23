<?php

require_once 'HTMLPurifier/AttrDef.php';
    require_once 'HTMLPurifier/AttrDef/Enum.php';
    require_once 'HTMLPurifier/AttrDef/ID.php';
    require_once 'HTMLPurifier/AttrDef/Class.php';
    require_once 'HTMLPurifier/AttrDef/Text.php';
    require_once 'HTMLPurifier/AttrDef/Lang.php';
    require_once 'HTMLPurifier/AttrDef/Pixels.php';
    require_once 'HTMLPurifier/AttrDef/Length.php';
    require_once 'HTMLPurifier/AttrDef/MultiLength.php';
    require_once 'HTMLPurifier/AttrDef/Integer.php';
    require_once 'HTMLPurifier/AttrDef/URI.php';
    require_once 'HTMLPurifier/AttrDef/CSS.php';
require_once 'HTMLPurifier/AttrTransform.php';
    require_once 'HTMLPurifier/AttrTransform/Lang.php';
    require_once 'HTMLPurifier/AttrTransform/TextAlign.php';
    require_once 'HTMLPurifier/AttrTransform/BdoDir.php';
    require_once 'HTMLPurifier/AttrTransform/ImgRequired.php';
require_once 'HTMLPurifier/ChildDef.php';
    require_once 'HTMLPurifier/ChildDef/Chameleon.php';
    require_once 'HTMLPurifier/ChildDef/Empty.php';
    require_once 'HTMLPurifier/ChildDef/Required.php';
    require_once 'HTMLPurifier/ChildDef/Optional.php';
    require_once 'HTMLPurifier/ChildDef/Table.php';
    require_once 'HTMLPurifier/ChildDef/StrictBlockquote.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Token.php';
require_once 'HTMLPurifier/TagTransform.php';

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
    'supported in the first place (like embed).  If you change this, you '.
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
 * Defines the purified HTML type with large amounts of objects.
 * 
 * The main function of this object is its $info array, which is an 
 * associative array of all the child and attribute definitions for
 * each allowed element. It also contains special use information (always
 * prefixed by info) for intelligent tag closing and global attributes.
 * 
 * For optimization, the definition generation may be moved to
 * a maintenance script and stipulate that definition be created
 * by a factory method that unserializes a serialized version of Definition.
 * Customization would entail copying the maintenance script, making the
 * necessary changes, generating the serialized object, and then hooking it
 * in via the factory method. We would also offer a LiveDefinition for
 * automatic recompilation, suggesting that we would have a DefinitionGenerator.
 */

class HTMLPurifier_HTMLDefinition
{
    
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
     * List of HTMLPurifier_AttrTransform to be performed after validation/
     * @public
     */
    var $info_attr_transform_post = array();
    
    /**
     * Lookup table of flow elements
     * @public
     */
    var $info_flow_elements = array();
    
    /**
     * Boolean is a strict definition?
     * @public
     */
    var $strict;
    
    /**
     * Initializes the definition, the meat of the class.
     */
    function setup($config) {
        
        // some cached config values
        $this->strict = $config->get('HTML', 'Strict');
        
        //////////////////////////////////////////////////////////////////////
        // info[] : initializes the definition objects
        
        // if you attempt to define rules later on for a tag not in this array
        // PHP will create an stdclass
        
        $allowed_tags =
            array(
                'ins', 'del', 'blockquote', 'dd', 'li', 'div', 'em', 'strong',
                'dfn', 'code', 'samp', 'kbd', 'var', 'cite', 'abbr', 'acronym',
                'q', 'sub', 'tt', 'sup', 'i', 'b', 'big', 'small',
                'bdo', 'span', 'dt', 'p', 'h1', 'h2', 'h3', 'h4',
                'h5', 'h6', 'ol', 'ul', 'dl', 'address', 'img', 'br', 'hr',
                'pre', 'a', 'table', 'caption', 'thead', 'tfoot', 'tbody',
                'colgroup', 'col', 'td', 'th', 'tr'
            );
        
        if (!$this->strict) {
            $allowed_tags[] = 'u';
            $allowed_tags[] = 's';
            $allowed_tags[] = 'strike';
        }
        
        foreach ($allowed_tags as $tag) {
            $this->info[$tag] = new HTMLPurifier_ElementDef();
        }
        
        //////////////////////////////////////////////////////////////////////
        // info[]->child : defines allowed children for elements
        
        // emulates the structure of the DTD
        // however, these are condensed, with bad stuff taken out
        // screening process was done by hand
        
        // entities: prefixed with e_ and _ replaces . from DTD
        // double underlines are entities we made up
        
        // we don't use an array because that complicates interpolation
        // strings are used instead of arrays because if you use arrays,
        // you have to do some hideous manipulation with array_merge()
        
        // todo: determine whether or not having allowed children
        //       that aren't allowed globally affects security (it shouldn't)
        // if above works out, extend children definitions to include all
        //       possible elements (allowed elements will dictate which ones
        //       get dropped
        
        $e_special_extra = 'img';
        $e_special_basic = 'br | span | bdo';
        $e_special = "$e_special_basic | $e_special_extra";
        $e_fontstyle_extra = 'big | small';
        $e_fontstyle_basic = 'tt | i | b | u | s | strike';
        $e_fontstyle = "$e_fontstyle_basic | $e_fontstyle_extra";
        $e_phrase_extra = 'sub | sup';
        $e_phrase_basic = 'em | strong | dfn | code | q | samp | kbd | var'.
          ' | cite | abbr | acronym';
        $e_phrase = "$e_phrase_basic | $e_phrase_extra";
        $e_misc_inline = 'ins | del';
        $e_misc = "$e_misc_inline";
        $e_inline = "a | $e_special | $e_fontstyle | $e_phrase";
        // pseudo-property we created for convenience, see later on
        $e__inline = "#PCDATA | $e_inline | $e_misc_inline";
        // note the casing
        $e_Inline = new HTMLPurifier_ChildDef_Optional($e__inline);
        $e_heading = 'h1|h2|h3|h4|h5|h6';
        $e_lists = 'ul | ol | dl';
        $e_blocktext = 'pre | hr | blockquote | address';
        $e_block = "p | $e_heading | div | $e_lists | $e_blocktext | table";
        $e_Block = new HTMLPurifier_ChildDef_Optional($e_block);
        $e__flow = "#PCDATA | $e_block | $e_inline | $e_misc";
        $e_Flow = new HTMLPurifier_ChildDef_Optional($e__flow);
        $e_a_content = new HTMLPurifier_ChildDef_Optional("#PCDATA".
          " | $e_special | $e_fontstyle | $e_phrase | $e_misc_inline");
        $e_pre_content = new HTMLPurifier_ChildDef_Optional("#PCDATA | a".
          " | $e_special_basic | $e_fontstyle_basic | $e_phrase_basic".
          " | $e_misc_inline");
        $e_form_content = new HTMLPurifier_ChildDef_Optional('');//unused
        $e_form_button_content = new HTMLPurifier_ChildDef_Optional('');//unused
        
        $this->info['ins']->child =
        $this->info['del']->child =
            new HTMLPurifier_ChildDef_Chameleon($e__inline, $e__flow);
        
        $this->info['dd']->child  =
        $this->info['li']->child  =
        $this->info['div']->child = $e_Flow;
        
        if ($this->strict) {
            $this->info['blockquote']->child = new HTMLPurifier_ChildDef_StrictBlockquote();
        } else {
            $this->info['blockquote']->child = $e_Flow;
        }
        
        $this->info['caption']->child   = 
        $this->info['em']->child   =
        $this->info['strong']->child    =
        $this->info['dfn']->child  =
        $this->info['code']->child =
        $this->info['samp']->child =
        $this->info['kbd']->child  =
        $this->info['var']->child  =
        $this->info['cite']->child =
        $this->info['abbr']->child =
        $this->info['acronym']->child   =
        $this->info['q']->child    =
        $this->info['sub']->child  =
        $this->info['tt']->child   =
        $this->info['sup']->child  =
        $this->info['i']->child    =
        $this->info['b']->child    =
        $this->info['big']->child  =
        $this->info['small']->child=
        $this->info['u']->child    =
        $this->info['s']->child    =
        $this->info['strike']->child    =
        $this->info['bdo']->child  =
        $this->info['span']->child =
        $this->info['dt']->child   =
        $this->info['p']->child    = 
        $this->info['h1']->child   = 
        $this->info['h2']->child   = 
        $this->info['h3']->child   = 
        $this->info['h4']->child   = 
        $this->info['h5']->child   = 
        $this->info['h6']->child   = $e_Inline;
        
        // the only three required definitions, besides custom table code
        $this->info['ol']->child   =
        $this->info['ul']->child   = new HTMLPurifier_ChildDef_Required('li');
        
        $this->info['dl']->child   = new HTMLPurifier_ChildDef_Required('dt|dd');
        
        if ($this->strict) {
            $this->info['address']->child = $e_Inline;
        } else {
            $this->info['address']->child =
              new HTMLPurifier_ChildDef_Optional("#PCDATA | p | $e_inline".
                  " | $e_misc_inline");
        }
        
        $this->info['img']->child  =
        $this->info['br']->child   =
        $this->info['hr']->child   = new HTMLPurifier_ChildDef_Empty();
        
        $this->info['pre']->child  = $e_pre_content;
        
        $this->info['a']->child    = $e_a_content;
        
        $this->info['table']->child = new HTMLPurifier_ChildDef_Table();
        
        // not a real entity, watch the double underscore
        $e__row = new HTMLPurifier_ChildDef_Required('tr');
        $this->info['thead']->child = $e__row;
        $this->info['tfoot']->child = $e__row;
        $this->info['tbody']->child = $e__row;
        $this->info['colgroup']->child = new HTMLPurifier_ChildDef_Optional('col');
        $this->info['col']->child = new HTMLPurifier_ChildDef_Empty();
        $this->info['tr']->child = new HTMLPurifier_ChildDef_Required('th | td');
        $this->info['th']->child = $e_Flow;
        $this->info['td']->child = $e_Flow;
        
        //////////////////////////////////////////////////////////////////////
        // info[]->type : defines the type of the element (block or inline)
        
        // reuses $e_Inline and $e_Block
        foreach ($e_Inline->elements as $name => $bool) {
            if ($name == '#PCDATA') continue;
            $this->info[$name]->type = 'inline';
        }
        
        foreach ($e_Block->elements as $name => $bool) {
            $this->info[$name]->type = 'block';
        }
        
        foreach ($e_Flow->elements as $name => $bool) {
            $this->info_flow_elements[$name] = true;
        }
        
        //////////////////////////////////////////////////////////////////////
        // info[]->excludes : defines elements that aren't allowed in here
        
        // make sure you test using isset() and not !empty()
        
        $this->info['a']->excludes = array('a' => true);
        $this->info['pre']->excludes = array_flip(array('img', 'big', 'small',
            // technically useless, but good to be indepth
            'object', 'applet', 'font', 'basefont'));
        
        //////////////////////////////////////////////////////////////////////
        // info[]->attr : defines allowed attributes for elements
        
        // this doesn't include REQUIRED declarations, those are handled
        // by the transform classes. It will, however, do simple and slightly
        // complex attribute value substitution
        
        // the question of varying allowed attributes is more entangling.
        
        $e_Text = new HTMLPurifier_AttrDef_Text();
        
        // attrs, included in almost every single one except for a few,
        // which manually override these in their local definitions
        $this->info_global_attr = array(
            // core attrs
            'class' => new HTMLPurifier_AttrDef_Class(),
            'title' => $e_Text,
            'style' => new HTMLPurifier_AttrDef_CSS(),
            // i18n
            'dir'   => new HTMLPurifier_AttrDef_Enum(array('ltr','rtl'), false),
            'lang'  => new HTMLPurifier_AttrDef_Lang(),
            'xml:lang' => new HTMLPurifier_AttrDef_Lang(),
            );
        
        if ($config->get('HTML', 'EnableAttrID')) {
            $this->info_global_attr['id'] = new HTMLPurifier_AttrDef_ID();
        }
        
        // required attribute stipulation handled in attribute transformation
        $this->info['bdo']->attr = array(); // nothing else
        
        $this->info['br']->attr['dir'] = false;
        $this->info['br']->attr['lang'] = false;
        $this->info['br']->attr['xml:lang'] = false;
        
        $this->info['td']->attr['abbr'] = $e_Text;
        $this->info['th']->attr['abbr'] = $e_Text;
        
        $this->setAttrForTableElements('align', new HTMLPurifier_AttrDef_Enum(
            array('left', 'center', 'right', 'justify', 'char'), false));
        
        $this->setAttrForTableElements('valign', new HTMLPurifier_AttrDef_Enum(
            array('top', 'middle', 'bottom', 'baseline'), false));
        
        $this->info['img']->attr['alt'] = $e_Text;
        
        $e_TFrame = new HTMLPurifier_AttrDef_Enum(array('void', 'above',
            'below', 'hsides', 'lhs', 'rhs', 'vsides', 'box', 'border'), false);
        $this->info['table']->attr['frame'] = $e_TFrame;
        
        $e_TRules = new HTMLPurifier_AttrDef_Enum(array('none', 'groups',
            'rows', 'cols', 'all'), false);
        $this->info['table']->attr['rules'] = $e_TRules;
        
        $this->info['table']->attr['summary'] = $e_Text;
        
        $this->info['table']->attr['border'] =
            new HTMLPurifier_AttrDef_Pixels();
        
        $e_Length = new HTMLPurifier_AttrDef_Length();
        $this->info['table']->attr['cellpadding'] =
        $this->info['table']->attr['cellspacing'] =
        $this->info['table']->attr['width'] =
        $this->info['img']->attr['height'] =
        $this->info['img']->attr['width'] = $e_Length;
        $this->setAttrForTableElements('charoff', $e_Length);
        
        $e_MultiLength = new HTMLPurifier_AttrDef_MultiLength();
        $this->info['col']->attr['width'] =
        $this->info['colgroup']->attr['width'] = $e_MultiLength;
        
        $e__NumberSpan = new HTMLPurifier_AttrDef_Integer(false, false, true);
        $this->info['colgroup']->attr['span'] =
        $this->info['col']->attr['span']   =
        $this->info['td']->attr['rowspan'] =
        $this->info['th']->attr['rowspan'] = 
        $this->info['td']->attr['colspan'] =
        $this->info['th']->attr['colspan'] = $e__NumberSpan;
        
        if (!$config->get('Attr', 'DisableURI')) {
            $e_URI = new HTMLPurifier_AttrDef_URI();
            $this->info['a']->attr['href'] =
            $this->info['img']->attr['longdesc'] =
            $this->info['del']->attr['cite'] =
            $this->info['ins']->attr['cite'] =
            $this->info['blockquote']->attr['cite'] =
            $this->info['q']->attr['cite'] = $e_URI;
            
            // URI that causes HTTP request
            $this->info['img']->attr['src'] = new HTMLPurifier_AttrDef_URI(true);
        }
        
        if (!$this->strict) {
            $this->info['li']->attr['value'] = new HTMLPurifier_AttrDef_Integer();
            $this->info['ol']->attr['start'] = new HTMLPurifier_AttrDef_Integer();
        }
        
        //////////////////////////////////////////////////////////////////////
        // info_tag_transform : transformations of tags
        
        $this->info_tag_transform['font']   = new HTMLPurifier_TagTransform_Font();
        $this->info_tag_transform['menu']   = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['dir']    = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['center'] = new HTMLPurifier_TagTransform_Center();
        
        //////////////////////////////////////////////////////////////////////
        // info[]->auto_close : tags that automatically close another
        
        // todo: determine whether or not SGML-like modeling based on
        // mandatory/optional end tags would be a better policy
        
        // make sure you test using isset() not !empty()
        
        // these are all block elements: blocks aren't allowed in P
        $this->info['p']->auto_close = array_flip(array(
                'address', 'blockquote', 'dd', 'dir', 'div', 'dl', 'dt',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'ol', 'p', 'pre',
                'table', 'ul'
            ));
        
        $this->info['li']->auto_close = array('li' => true);
        
        // we need TABLE and heading mismatch code
        // we may need to make this more flexible for heading mismatch,
        // or we can just create another info
        
        //////////////////////////////////////////////////////////////////////
        // info[]->attr_transform_* : attribute transformations in elements
        // pre is applied before any validation is done, post is done after
        
        $this->info['h1']->attr_transform_pre[] =
        $this->info['h2']->attr_transform_pre[] =
        $this->info['h3']->attr_transform_pre[] =
        $this->info['h4']->attr_transform_pre[] =
        $this->info['h5']->attr_transform_pre[] =
        $this->info['h6']->attr_transform_pre[] =
        $this->info['p'] ->attr_transform_pre[] = 
                    new HTMLPurifier_AttrTransform_TextAlign();
        
        $this->info['bdo']->attr_transform_post[] =
                    new HTMLPurifier_AttrTransform_BdoDir();
        
        $this->info['img']->attr_transform_post[] =
                    new HTMLPurifier_AttrTransform_ImgRequired();
        
        //////////////////////////////////////////////////////////////////////
        // info_attr_transform_* : global attribute transformation that is
        // unconditionally called. Good for transformations that have complex
        // start conditions
        // pre is applied before any validation is done, post is done after
        
        $this->info_attr_transform_post[] = new HTMLPurifier_AttrTransform_Lang();
        
        // protect against stdclasses floating around
        foreach ($this->info as $key => $obj) {
            if (is_a($obj, 'stdclass')) {
                unset($this->info[$key]);
            }
        }
        
        //////////////////////////////////////////////////////////////////////
        // info_block_wrapper : wraps inline elements in block context
        
        $block_wrapper = $config->get('HTML', 'BlockWrapper');
        if (isset($e_Block->elements[$block_wrapper])) {
            $this->info_block_wrapper = $block_wrapper;
        } else {
            trigger_error('Cannot use non-block element as block wrapper.',
                E_USER_ERROR);
        }
        
        //////////////////////////////////////////////////////////////////////
        // info_parent : parent element of the HTML fragment
        
        $parent = $config->get('HTML', 'Parent');
        if (isset($this->info[$parent])) {
            $this->info_parent = $parent;
        } else {
            trigger_error('Cannot use unrecognized element as parent.',
                E_USER_ERROR);
        }
        $this->info_parent_def = $this->info[$this->info_parent];
        
        //////////////////////////////////////////////////////////////////////
        // %HTML.Allowed(Elements|Attributes) : cut non-allowed elements
        $allowed_elements = $config->get('HTML', 'AllowedElements');
        if (is_array($allowed_elements)) {
            // $allowed_elements[$this->info_parent] = true; // allow parent element
            foreach ($this->info as $name => $d) {
                if(!isset($allowed_elements[$name])) unset($this->info[$name]);
            }
        }
        $allowed_attributes = $config->get('HTML', 'AllowedAttributes');
        if (is_array($allowed_attributes)) {
            foreach ($this->info_global_attr as $attr => $info) {
                if (!isset($allowed_attributes["*.$attr"])) {
                    unset($this->info_global_attr[$attr]);
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
    
    function setAttrForTableElements($attr, $def) {
        $this->info['col']->attr[$attr] = 
        $this->info['colgroup']->attr[$attr] = 
        $this->info['tbody']->attr[$attr] = 
        $this->info['td']->attr[$attr] = 
        $this->info['tfoot']->attr[$attr] = 
        $this->info['th']->attr[$attr] = 
        $this->info['thead']->attr[$attr] = 
        $this->info['tr']->attr[$attr] = $def;
    }
    
}

/**
 * Structure that stores an element definition.
 */
class HTMLPurifier_ElementDef
{
    
    /**
     * Associative array of attribute name to HTMLPurifier_AttrDef
     * @public
     */
    var $attr = array();
    
    /**
     * List of tag's HTMLPurifier_AttrTransform to be done before validation
     * @public
     */
    var $attr_transform_pre = array();
    
    /**
     * List of tag's HTMLPurifier_AttrTransform to be done after validation
     * @public
     */
    var $attr_transform_post = array();
    
    /**
     * Lookup table of tags that close this tag.
     * @public
     */
    var $auto_close = array();
    
    /**
     * HTMLPurifier_ChildDef of this tag.
     * @public
     */
    var $child;
    
    /**
     * Type of the tag: inline or block or unknown?
     * @public
     */
    var $type = 'unknown';
    
    /**
     * Lookup table of tags excluded from all descendants of this tag.
     * @public
     */
    var $excludes = array();
    
}

?>