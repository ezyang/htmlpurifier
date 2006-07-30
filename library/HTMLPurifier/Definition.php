<?php

require_once 'HTMLPurifier/AttrDef.php';
    require_once 'HTMLPurifier/AttrDef/Enum.php';
    require_once 'HTMLPurifier/AttrDef/ID.php';
require_once 'HTMLPurifier/ChildDef.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Token.php';

class HTMLPurifier_Definition
{
    
    var $generator;
    var $info = array();
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
        
        $this->info['child'] = array();
        
        $this->info['child']['ins'] =
        $this->info['child']['del'] = 
        $this->info['child']['blockquote'] =
        $this->info['child']['dd']  =
        $this->info['child']['li']  =
        $this->info['child']['div'] = $e_Flow;
        
        $this->info['child']['em']  =
        $this->info['child']['strong'] =
        $this->info['child']['dfn']  =
        $this->info['child']['code'] =
        $this->info['child']['samp'] =
        $this->info['child']['kbd']  =
        $this->info['child']['var']  =
        $this->info['child']['code'] =
        $this->info['child']['samp'] =
        $this->info['child']['kbd']  =
        $this->info['child']['var']  =
        $this->info['child']['cite'] =
        $this->info['child']['abbr'] =
        $this->info['child']['acronym'] =
        $this->info['child']['q']    =
        $this->info['child']['sub']  =
        $this->info['child']['tt']   =
        $this->info['child']['sup']  =
        $this->info['child']['i']    =
        $this->info['child']['b']    =
        $this->info['child']['big']  =
        $this->info['child']['small'] =
        $this->info['child']['u']    =
        $this->info['child']['s']    =
        $this->info['child']['strike'] =
        $this->info['child']['bdo']  =
        $this->info['child']['span'] =
        $this->info['child']['dt']   =
        $this->info['child']['p']    = 
        $this->info['child']['h1']   = 
        $this->info['child']['h2']   = 
        $this->info['child']['h3']   = 
        $this->info['child']['h4']   = 
        $this->info['child']['h5']   = 
        $this->info['child']['h6']   = $e_Inline;
        
        $this->info['child']['ol']   =
        $this->info['child']['ul']   = new HTMLPurifier_ChildDef_Required('li');
        
        $this->info['child']['dl']   = new HTMLPurifier_ChildDef_Required('dt|dd');
        $this->info['child']['address'] =
          new HTMLPurifier_ChildDef_Optional("#PCDATA | p | $e_inline".
              " | $e_misc_inline");
        
        $this->info['child']['img']  =
        $this->info['child']['br']   =
        $this->info['child']['hr']   = new HTMLPurifier_ChildDef_Empty();
        
        $this->info['child']['pre']  = $e_pre_content;
        
        $this->info['child']['a']    = $e_a_content;
        
        // attribute info
        // this doesn't include REQUIRED declarations, those are handled
        // by the transform classes
        
        // attrs, included in almost every single one except for a few
        $this->info['attr']['*'] = array(
            // core attrs
            'id' => new HTMLPurifier_AttrDef_ID(),
            // i18n
            'dir' => new HTMLPurifier_AttrDef_Enum(array('ltr','rtl'), false),
            );
        
    }
    
}

class HTMLPurifier_ElementDef
{
    
    var $child_def;
    var $attr_def = array();
    
    function HTMLPurifier_ElementDef($child_def, $attr_def = array()) {
        $this->child_def = $child_def;
        $this->attr_def  = $attr_def;
    }
    
}

?>