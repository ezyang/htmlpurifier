<?php

require_once 'HTMLPurifier/HTMLModule/Tidy.php';

class HTMLPurifier_HTMLModule_Tidy_Proprietary extends
      HTMLPurifier_HTMLModule_Tidy
{
    
    var $name = 'Tidy_Proprietary';
    var $defaultLevel = 'light';
    
    function makeFixes() {
        $r = array();
        
        // {{{ // duplicated from XHTMLAndHTML4: not sure how to factor out
            $align_lookup = array();
            $align_values = array('left', 'right', 'center', 'justify');
            foreach ($align_values as $v) $align_lookup[$v] = "text-align:$v;";
        // }}}
        $r['div@align'] = new HTMLPurifier_AttrTransform_EnumToCSS('align', $align_lookup);
        
        return $r;
    }
    
}

?>