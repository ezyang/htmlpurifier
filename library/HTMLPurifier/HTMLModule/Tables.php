<?php

require_once 'HTMLPurifier/HTMLModule.php';

/**
 * XHTML 1.1 Tables Module, fully defines accessible table elements.
 */
class HTMLPurifier_HTMLModule_Tables extends HTMLPurifier_HTMLModule
{
    
    var $elements = array('caption', 'table', 'td', 'th', 'tr', 'col',
        'colgroup', 'tbody', 'thead', 'tfoot');
    var $info = array();
    var $content_sets = array('Block' => 'table');
    
    function HTMLPurifier_HTMLModule_Tables() {
        foreach ($this->elements as $e) {
            $this->info[$e] = new HTMLPurifier_ElementDef();
            $this->info[$e]->attr = array(0 => array('Common'));
            $attr =& $this->info[$e]->attr;
            if ($e == 'caption') continue;
            if ($e == 'table'){
                $attr['border'] = 'Pixels';
                $attr['cellpadding'] = 'Length';
                $attr['cellspacing'] = 'Length';
                $attr['frame'] = new HTMLPurifier_AttrDef_Enum(array(
                    'void', 'above', 'below', 'hsides', 'lhs', 'rhs',
                    'vsides', 'box', 'border'
                ), false);
                $attr['rules'] = new HTMLPurifier_AttrDef_Enum(array(
                    'none', 'groups', 'rows', 'cols', 'all'
                ), false);
                $attr['summary'] = 'Text';
                $attr['width'] = 'Length';
                continue;
            }
            if ($e == 'td' || $e == 'th') $attr['abbr'] = 'Text';
            $attr['align'] = new HTMLPurifier_AttrDef_Enum(array(
                'left', 'center', 'right', 'justify', 'char'
            ), false);
            $attr['valign'] = new HTMLPurifier_AttrDef_Enum(array(
                'top', 'middle', 'bottom', 'baseline'
            ), false);
            $attr['charoff'] = 'Length';
        }
        $this->info['caption']->content_model = '#PCDATA | Inline';
        $this->info['caption']->content_model_type = 'optional';
        
        $this->info['table']->content_model = 'caption?, ( col* | colgroup* ), (( thead?, tfoot?, tbody+ ) | ( tr+ ))';
        $this->info['table']->content_model_type = 'table';
        
        $this->info['td']->content_model = 
        $this->info['th']->content_model = '#PCDATA | Flow';
        $this->info['td']->content_model_type = 
        $this->info['th']->content_model_type = 'optional';
        
        $this->info['tr']->content_model = 'td | th';
        $this->info['tr']->content_model_type = 'required';
        
        $this->info['col']->content_model_type = 'empty';
        
        $this->info['colgroup']->content_model = 'col';
        $this->info['colgroup']->content_model_type = 'optional';
        
        $this->info['tbody']->content_model = 
        $this->info['thead']->content_model = 
        $this->info['tfoot']->content_model = 'tr';
        $this->info['tbody']->content_model_type = 
        $this->info['thead']->content_model_type = 
        $this->info['tfoot']->content_model_type = 'required';
        
    }
    
}

?>