<?php

require_once 'HTMLPurifier/HTMLModule/Tidy.php';

class HTMLPurifier_HTMLModule_Tidy_XHTMLStrict extends
      HTMLPurifier_HTMLModule_Tidy
{
    
    var $name = 'Tidy_XHTMLStrict';
    var $defaultLevel = 'light';
    
    function makeFixes() {
        $r = array();
        $r['blockquote#child'] = false;
        $r['blockquote#content_model_type'] = 'strictblockquote';
        return $r;
    }
    
    var $defines_child_def = true;
    function getChildDef($def) {
        if ($def->content_model_type != 'strictblockquote') return false;
        return new HTMLPurifier_ChildDef_StrictBlockquote($def->content_model);
    }
    
}

?>