<?php

require_once 'HTMLPurifier/AttrDef.php';
    require_once 'HTMLPurifier/AttrDef/Enum.php';
    require_once 'HTMLPurifier/AttrDef/ID.php';
require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Token.php';

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
    
    // used solely by HTMLPurifier_Strategy_MakeWellFormed
    var $info_closes_p = array(
        // these are all block elements: blocks aren't allowed in P
        'address'       => true,
        'blockquote'    => true,
        'dd'            => true,
        'dir'           => true,
        'div'           => true, 
        'dl'            => true,
        'dt'            => true,
        'h1'            => true,
        'h2'            => true,
        'h3'            => true,
        'h4'            => true, 
        'h5'            => true,
        'h6'            => true,
        'hr'            => true,
        'ol'            => true,
        'p'             => true,
        'pre'           => true, 
        'table'         => true,
        'ul'            => true
        );
    
    // used solely by HTMLPurifier_Strategy_ValidateAttributes
    var $info_global_attr = array();
    
    // used solely by HTMLPurifier_Strategy_FixNesting
    var $info_parent = 'div';
    
    function instance() {
        static $instance = null;
        if (!$instance) {
            $instance = new HTMLPurifier_Definition();
            $instance->setup();
        }
        return $instance;
    }
    
    function HTMLPurifier_Definition() {}
    
    function setup() {
        // emulates the structure of the DTD
        
        $allowed_tags =
            array(
                'ins', 'del', 'blockquote', 'dd', 'li', 'div', 'em', 'strong',
                'dfn', 'code', 'samp', 'kbd', 'var', 'cite', 'abbr', 'acronym',
                'q', 'sub', 'tt', 'sup', 'i', 'b', 'big', 'small', 'u', 's',
                'strike', 'bdo', 'span', 'dt', 'p', 'h1', 'h2', 'h3', 'h4',
                'h5', 'h6', 'ol', 'ul', 'dl', 'address', 'img', 'br', 'hr',
                'pre', 'a'
            );
        
        foreach ($allowed_tags as $tag) {
            $this->info[$tag] = new HTMLPurifier_ElementDef();
        }
        
        // entities: prefixed with e_ and _ replaces .
        // we don't use an array because that complicates interpolation
        // strings are used instead of arrays because if you use arrays,
        // you have to do some hideous manipulation with array_merge()
        
        // these are condensed, remember, with bad stuff taken out
        
        // transforms: font, menu, dir, center
        
        // DON'T MONKEY AROUND THIS unless you know what you are doing
        // and also know the assumptions the code makes about what this
        // contains for optimization purposes (see fixNesting)
        
        // child info
        
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
        // note the casing
        $e_Inline = new HTMLPurifier_ChildDef_Optional("#PCDATA | $e_inline".
          " | $e_misc_inline");
        $e_heading = 'h1|h2|h3|h4|h5|h6';
        $e_lists = 'ul | ol | dl';
        $e_blocktext = 'pre | hr | blockquote | address';
        $e_block = "p | $e_heading | div | $e_lists | $e_blocktext | table";
        $e_Flow = new HTMLPurifier_ChildDef_Optional("#PCDATA | $e_block".
          " | $e_inline | $e_misc");
        $e_a_content = new HTMLPurifier_ChildDef_Optional("#PCDATA | $e_special".
          " | $e_fontstyle | $e_phrase | $e_inline_forms | $e_misc_inline");
        $e_pre_content = new HTMLPurifier_ChildDef_Optional("#PCDATA | a".
          " | $e_special_basic | $e_fontstyle_basic | $e_phrase_basic".
          " | $e_inline_forms | $e_misc_inline");
        $e_form_content = new HTMLPurifier_ChildDef_Optional(''); //unused
        $e_form_button_content = new HTMLPurifier_ChildDef_Optional(''); // unused
        
        $this->info['ins']->child =
        $this->info['del']->child = 
        $this->info['blockquote']->child =
        $this->info['dd']->child  =
        $this->info['li']->child  =
        $this->info['div']->child = $e_Flow;
        
        $this->info['em']->child   =
        $this->info['strong']->child =
        $this->info['dfn']->child  =
        $this->info['code']->child =
        $this->info['samp']->child =
        $this->info['kbd']->child  =
        $this->info['var']->child  =
        $this->info['cite']->child =
        $this->info['abbr']->child =
        $this->info['acronym']->child =
        $this->info['q']->child    =
        $this->info['sub']->child  =
        $this->info['tt']->child   =
        $this->info['sup']->child  =
        $this->info['i']->child    =
        $this->info['b']->child    =
        $this->info['big']->child  =
        $this->info['small']->child =
        $this->info['u']->child    =
        $this->info['s']->child    =
        $this->info['strike']->child =
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
        
        // attribute info
        // this doesn't include REQUIRED declarations, those are handled
        // by the transform classes
        
        // attrs, included in almost every single one except for a few
        $this->info_global_attr = array(
            // core attrs
            'id' => new HTMLPurifier_AttrDef_ID(),
            // i18n
            'dir' => new HTMLPurifier_AttrDef_Enum(array('ltr','rtl'), false),
            );
        
    }
    
}

class HTMLPurifier_ElementDef
{
    
    var $child;
    var $attr = array();
    
}

?>