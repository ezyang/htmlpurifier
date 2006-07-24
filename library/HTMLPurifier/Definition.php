<?php

require_once 'HTMLPurifier/AttrDef.php';
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
        
        $this->info['ins'] =
        $this->info['del'] = 
        $this->info['blockquote'] =
        $this->info['dd']  =
        $this->info['li']  =
        $this->info['div'] = new HTMLPurifier_ElementDef($e_Flow);
        
        $this->info['em']  =
        $this->info['strong'] =
        $this->info['dfn']  =
        $this->info['code'] =
        $this->info['samp'] =
        $this->info['kbd']  =
        $this->info['var']  =
        $this->info['code'] =
        $this->info['samp'] =
        $this->info['kbd']  =
        $this->info['var']  =
        $this->info['cite'] =
        $this->info['abbr'] =
        $this->info['acronym'] =
        $this->info['q']    =
        $this->info['sub']  =
        $this->info['tt']   =
        $this->info['sup']  =
        $this->info['i']    =
        $this->info['b']    =
        $this->info['big']  =
        $this->info['small'] =
        $this->info['u']    =
        $this->info['s']    =
        $this->info['strike'] =
        $this->info['bdo']  =
        $this->info['span'] =
        $this->info['dt']   =
        $this->info['p']    = 
        $this->info['h1']   = 
        $this->info['h2']   = 
        $this->info['h3']   = 
        $this->info['h4']   = 
        $this->info['h5']   = 
        $this->info['h6']   = new HTMLPurifier_ElementDef($e_Inline);
        
        $this->info['ol']   =
        $this->info['ul']   =
          new HTMLPurifier_ElementDef(
            new HTMLPurifier_ChildDef_Required('li')
          );
        
        $this->info['dl']   =
          new HTMLPurifier_ElementDef(
            new HTMLPurifier_ChildDef_Required('dt|dd')
          );
        $this->info['address'] =
          new HTMLPurifier_ElementDef(
            new HTMLPurifier_ChildDef_Optional("#PCDATA | p | $e_inline".
              " | $e_misc_inline")
          );
        
        $this->info['img']  =
        $this->info['br']   =
        $this->info['hr']   = new HTMLPurifier_ElementDef(new HTMLPurifier_ChildDef_Empty());
        
        $this->info['pre']  = new HTMLPurifier_ElementDef($e_pre_content);
        
        $this->info['a']    = new HTMLPurifier_ElementDef($e_a_content);
        
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