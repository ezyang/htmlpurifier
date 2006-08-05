<?php

require_once 'HTMLPurifier/AttrDef.php';
    require_once 'HTMLPurifier/AttrDef/Enum.php';
    require_once 'HTMLPurifier/AttrDef/ID.php';
    require_once 'HTMLPurifier/AttrDef/Class.php';
    require_once 'HTMLPurifier/AttrDef/Text.php';
    require_once 'HTMLPurifier/AttrDef/Lang.php';
require_once 'HTMLPurifier/AttrTransform.php';
    require_once 'HTMLPurifier/AttrTransform/Lang.php';
    require_once 'HTMLPurifier/AttrTransform/TextAlign.php';
require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Token.php';
require_once 'HTMLPurifier/TagTransform.php';

/**
 * Defines the purified HTML type with large amounts of objects.
 * 
 * The main function of this object is its $info array, which is an 
 * associative array of all the child and attribute definitions for
 * each allowed element. It also contains special use information (always
 * prefixed by info) for intelligent tag closing and global attributes.
 * 
 * Planned improvements include attribute transformation objects as well as
 * migration of auto-tag-closing from HTMLPurifier_Strategy_MakeWellFormed
 * (these can likely just be extensions of ElementDef).
 * 
 * After development drops off, the definition generation will be moved to
 * a maintenance script and we will stipulate that definition be created
 * by a factory method that unserializes a serialized version of Definition.
 * Customization would entail copying the maintenance script, making the
 * necessary changes, generating the serialized object, and then hooking it
 * in via the factory method. We would also offer a LiveDefinition for
 * automatic recompilation, suggesting that we would have a DefinitionGenerator.
 */

class HTMLPurifier_Definition
{
    
    var $info = array();
    
    // used solely by HTMLPurifier_Strategy_ValidateAttributes
    var $info_global_attr = array();
    
    // used solely by HTMLPurifier_Strategy_FixNesting
    var $info_parent = 'div';
    
    // used solely by HTMLPurifier_Strategy_RemoveForeignElements
    var $info_tag_transform = array();
    
    // used solely by HTMLPurifier_Strategy_ValidateAttributes
    var $info_attr_transform = array();
    
    // WARNING! Prototype is not passed by reference, so in order to get
    // a copy of the real one, you'll have to destroy your copy and
    // use instance() to get it.
    // Usually, however, modifying the returned definition (reference) should be
    // sufficient
    function &instance($prototype = null) {
        static $instance = null;
        if ($prototype) {
            $instance = $prototype;
        } elseif (!$instance) {
            $instance = new HTMLPurifier_Definition();
            $instance->setup();
        }
        return $instance;
    }
    
    function HTMLPurifier_Definition() {}
    
    function setup() {
        
        // emulates the structure of the DTD
        // these are condensed, however, with bad stuff taken out
        // screening process was done by hand
        
        //////////////////////////////////////////////////////////////////////
        // info[] : initializes the definition objects
        
        // if you attempt to define rules later on for a tag not in this array
        // PHP will create an stdclass
        
        $allowed_tags =
            array(
                'ins', 'del', 'blockquote', 'dd', 'li', 'div', 'em', 'strong',
                'dfn', 'code', 'samp', 'kbd', 'var', 'cite', 'abbr', 'acronym',
                'q', 'sub', 'tt', 'sup', 'i', 'b', 'big', 'small', 'u', 's',
                'strike', 'bdo', 'span', 'dt', 'p', 'h1', 'h2', 'h3', 'h4',
                'h5', 'h6', 'ol', 'ul', 'dl', 'address', 'img', 'br', 'hr',
                'pre', 'a', 'table', 'caption', 'thead', 'tfoot', 'tbody',
                'colgroup', 'col', 'td', 'th', 'tr'
            );
        
        foreach ($allowed_tags as $tag) {
            $this->info[$tag] = new HTMLPurifier_ElementDef();
        }
        
        //////////////////////////////////////////////////////////////////////
        // info[]->child : defines allowed children for elements
        
        // entities: prefixed with e_ and _ replaces .
        
        // we don't use an array because that complicates interpolation
        // strings are used instead of arrays because if you use arrays,
        // you have to do some hideous manipulation with array_merge()
        
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
        $e_inline_forms = ''; // humor the dtd
        $e_misc_inline = 'ins | del';
        $e_misc = "$e_misc_inline";
        $e_inline = "a | $e_special | $e_fontstyle | $e_phrase".
          " | $e_inline_forms";
        $e__inline = "#PCDATA | $e_inline | $e_misc_inline";
        // note the casing
        $e_Inline = new HTMLPurifier_ChildDef_Optional($e__inline);
        $e_heading = 'h1|h2|h3|h4|h5|h6';
        $e_lists = 'ul | ol | dl';
        $e_blocktext = 'pre | hr | blockquote | address';
        $e_block = "p | $e_heading | div | $e_lists | $e_blocktext | table";
        $e__flow = "#PCDATA | $e_block | $e_inline | $e_misc";
        $e_Flow = new HTMLPurifier_ChildDef_Optional($e__flow);
        $e_a_content = new HTMLPurifier_ChildDef_Optional("#PCDATA | $e_special".
          " | $e_fontstyle | $e_phrase | $e_inline_forms | $e_misc_inline");
        $e_pre_content = new HTMLPurifier_ChildDef_Optional("#PCDATA | a".
          " | $e_special_basic | $e_fontstyle_basic | $e_phrase_basic".
          " | $e_inline_forms | $e_misc_inline");
        $e_form_content = new HTMLPurifier_ChildDef_Optional(''); //unused
        $e_form_button_content = new HTMLPurifier_ChildDef_Optional(''); // unused
        
        $this->info['ins']->child =
        $this->info['del']->child = new HTMLPurifier_ChildDef_Chameleon($e__inline, $e__flow);
        
        $this->info['blockquote']->child=
        $this->info['dd']->child  =
        $this->info['li']->child  =
        $this->info['div']->child = $e_Flow;
        
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
        
        $this->info['address']->child =
          new HTMLPurifier_ChildDef_Optional("#PCDATA | p | $e_inline".
              " | $e_misc_inline");
        
        $this->info['img']->child  =
        $this->info['br']->child   =
        $this->info['hr']->child   = new HTMLPurifier_ChildDef_Empty();
        
        $this->info['pre']->child  = $e_pre_content;
        
        $this->info['a']->child    = $e_a_content;
        
        $this->info['table']->child = new HTMLPurifier_ChildDef_Custom(
            '(caption?, (col*|colgroup*), thead?, tfoot?, (tbody+|tr+))');
        
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
        
        // reuses $e_Inline and $e_block
        
        foreach ($e_Inline->elements as $name) {
            $this->info[$name]->type = 'inline';
        }
        
        $e_Block = new HTMLPurifier_ChildDef_Optional($e_block);
        foreach ($e_Block->elements as $name) {
            $this->info[$name]->type = 'block';
        }
        
        //////////////////////////////////////////////////////////////////////
        // info[]->excludes : defines elements that aren't allowed in here
        
        // make sure you test using isset() and not !empty()
        
        $this->info['a']->excludes = array('a' => true);
        $this->info['pre']->excludes = array_flip(array('img', 'big', 'small',
            // technically in spec, but we don't allow em anyway
            'object', 'applet', 'font', 'basefont'));
        
        //////////////////////////////////////////////////////////////////////
        // info[]->attr : defines allowed attributes for elements
        
        // this doesn't include REQUIRED declarations, those are handled
        // by the transform classes. It will, however, do simple and slightly
        // complex attribute value substitution
        
        // attrs, included in almost every single one except for a few,
        // which manually override these in their local definitions
        $this->info_global_attr = array(
            // core attrs
            'id'    => new HTMLPurifier_AttrDef_ID(),
            'class' => new HTMLPurifier_AttrDef_Class(),
            'title' => new HTMLPurifier_AttrDef_Text(),
            // i18n
            'dir'   => new HTMLPurifier_AttrDef_Enum(array('ltr','rtl'), false),
            'lang'  => new HTMLPurifier_AttrDef_Lang(),
            'xml:lang' => new HTMLPurifier_AttrDef_Lang(),
            );
        
        // required attribute stipulation handled in attribute transformation
        $this->info['bdo']->attr = array();
        
        $this->info['br']->attr = array(
            'dir' => false,
            'lang' => false,
            'xml:lang' => false,
            );
        
        //////////////////////////////////////////////////////////////////////
        // UNIMP : info_tag_transform : transformations of tags
        
        $this->info_tag_transform['font']   = new HTMLPurifier_TagTransform_Font();
        $this->info_tag_transform['menu']   = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['dir']    = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['center'] = new HTMLPurifier_TagTransform_Center();
        
        //////////////////////////////////////////////////////////////////////
        // info[]->auto_close : tags that automatically close another
        
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
        // info[]->attr_transform : attribute transformations in elements
        
        $transform = new HTMLPurifier_AttrTransform_TextAlign();
        $this->info['h1']->attr_transform[] =
        $this->info['h2']->attr_transform[] =
        $this->info['h3']->attr_transform[] =
        $this->info['h4']->attr_transform[] =
        $this->info['h5']->attr_transform[] =
        $this->info['h6']->attr_transform[] =
        $this->info['p'] ->attr_transform[] = $transform;
        
        //////////////////////////////////////////////////////////////////////
        // info_attr_transform : global attribute transformation that is
        // unconditionally called. Good for transformations that have complex
        // start conditions
        
        $this->info_attr_transform[] = new HTMLPurifier_AttrTransform_Lang();
        
    }
    
}

class HTMLPurifier_ElementDef
{
    
    var $attr = array();
    var $attr_transform = array();
    var $auto_close = array();
    var $child;
    var $type = 'unknown';
    var $excludes = array();
    
}

?>