<?php

require_once 'HTMLPurifier/ChildDef/StrictBlockquote.php';

/**
 * Proprietary module that transforms deprecated elements into Strict
 * HTML (see HTML 4.01 and XHTML 1.0) when possible.
 */

class HTMLPurifier_HTMLModule_TransformToStrict extends HTMLPurifier_HTMLModule
{
    
    var $name = 'TransformToStrict';
    var $type = 'redefine';
    
    // we're actually modifying these elements, not defining them
    var $elements = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote');
    
    var $info_tag_transform = array(
        // placeholders, see constructor for definitions
        'font'  => false,
        'menu'  => false,
        'dir'   => false,
        'center'=> false
    );
    
    var $attr_collections = array(
        'Lang' => array(
            'lang' => false // placeholder
        )
    );
    
    var $info_attr_transform_post = array(
        'lang' => false // placeholder
    );
    
    function HTMLPurifier_HTMLModule_TransformToStrict() {
        
        // deprecated tag transforms
        $this->info_tag_transform['font']   = new HTMLPurifier_TagTransform_Font();
        $this->info_tag_transform['menu']   = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['dir']    = new HTMLPurifier_TagTransform_Simple('ul');
        $this->info_tag_transform['center'] = new HTMLPurifier_TagTransform_Center();
        
        foreach ($this->elements as $name) {
            $this->info[$name] = new HTMLPurifier_ElementDef();
            $this->info[$name]->standalone = false;
        }
        
        // deprecated attribute transforms
        $this->info['h1']->attr_transform_pre['align'] =
        $this->info['h2']->attr_transform_pre['align'] =
        $this->info['h3']->attr_transform_pre['align'] =
        $this->info['h4']->attr_transform_pre['align'] =
        $this->info['h5']->attr_transform_pre['align'] =
        $this->info['h6']->attr_transform_pre['align'] =
        $this->info['p'] ->attr_transform_pre['align'] = 
                    new HTMLPurifier_AttrTransform_TextAlign();
        
        // xml:lang <=> lang mirroring, implement in TransformToStrict,
        // this is overridden in TransformToXHTML11
        $this->info_attr_transform_post['lang'] = new HTMLPurifier_AttrTransform_Lang();
        $this->attr_collections['Lang']['lang'] = new HTMLPurifier_AttrDef_Lang();
        
        // this should not be applied to XHTML 1.0 Transitional, ONLY
        // XHTML 1.0 Strict. We may need three classes
        $this->info['blockquote']->content_model_type = 'strictblockquote';
        $this->info['blockquote']->child = false; // recalculate please!
        
    }
    
    var $defines_child_def = true;
    function getChildDef($def) {
        if ($def->content_model_type != 'strictblockquote') return false;
        return new HTMLPurifier_ChildDef_StrictBlockquote($def->content_model);
    }
    
}

?>