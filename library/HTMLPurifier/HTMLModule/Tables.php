<?php

require_once 'HTMLPurifier/HTMLModule.php';
require_once 'HTMLPurifier/ChildDef/Table.php';

/**
 * XHTML 1.1 Tables Module, fully defines accessible table elements.
 */
class HTMLPurifier_HTMLModule_Tables extends HTMLPurifier_HTMLModule
{
    
    var $name = 'Tables';
    
    function HTMLPurifier_HTMLModule_Tables() {
        
        $this->addElement('caption', true, false, 'Inline', 'Common');
        
        $this->addElement('table', true, 'Block', 
            new HTMLPurifier_ChildDef_Table(),  'Common', 
            array(
                'border' => 'Pixels',
                'cellpadding' => 'Length',
                'cellspacing' => 'Length',
                'frame' => new HTMLPurifier_AttrDef_Enum(array(
                    'void', 'above', 'below', 'hsides', 'lhs', 'rhs',
                    'vsides', 'box', 'border'
                ), false),
                'rules' => new HTMLPurifier_AttrDef_Enum(array(
                    'none', 'groups', 'rows', 'cols', 'all'
                ), false),
                'summary' => 'Text',
                'width' => 'Length'
            )
        );
        
        // common attributes
        $cell_align = array(
            'align' => new HTMLPurifier_AttrDef_Enum(array(
                'left', 'center', 'right', 'justify', 'char'
            ), false),
            'charoff' => 'Length',
            'valign' => new HTMLPurifier_AttrDef_Enum(array(
                'top', 'middle', 'bottom', 'baseline'
            ), false),
        );
        
        $cell_t = array_merge(
            array(
                'abbr'    => 'Text',
                'colspan' => 'Number',
                'rowspan' => 'Number',
            ),
            $cell_align
        );
        $this->addElement('td', true, false, 'Flow', 'Common', $cell_t);
        $this->addElement('th', true, false, 'Flow', 'Common', $cell_t);
        
        $this->addElement('tr', true, false, 'Required: td | th', 'Common', $cell_align);
        
        $cell_col = array_merge(
            array(
                'span'  => 'Number',
                'width' => 'MultiLength',
            ),
            $cell_align
        );
        $this->addElement('col',      true, false, 'Empty',         'Common', $cell_col);
        $this->addElement('colgroup', true, false, 'Optional: col', 'Common', $cell_col);
        
        $this->addElement('tbody', true, false, 'Required: tr', 'Common', $cell_align);
        $this->addElement('thead', true, false, 'Required: tr', 'Common', $cell_align);
        $this->addElement('tfoot', true, false, 'Required: tr', 'Common', $cell_align);
        
    }
    
}

?>