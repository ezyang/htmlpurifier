<?php

require_once 'HTMLPurifier/AttrDef/HTML/Bool.php';

/**
 * XHTML 1.1 Legacy module defines elements that were previously 
 * deprecated.
 * 
 * @note Not all legacy elements have been implemented yet, which
 *       is a bit of a reverse problem as compared to browsers! In
 *       addition, this legacy module may implement a bit more than
 *       mandated by XHTML 1.1.
 * 
 * This module can be used in combination with TransformToStrict in order
 * to transform as many deprecated elements as possible, but retain
 * questionably deprecated elements that do not have good alternatives
 * as well as transform elements that don't have an implementation.
 * See docs/ref-strictness.txt for more details.
 */

class HTMLPurifier_HTMLModule_Legacy extends HTMLPurifier_HTMLModule
{
    
    // incomplete
    
    var $name = 'Legacy';
    
    function HTMLPurifier_HTMLModule_Legacy() {
        
        $this->addElement('basefont', true, 'Inline', 'Empty', false, array(
            'color' => 'Color',
            'face' => 'Text', // extremely broad, we should
            'size' => 'Text', // tighten it
            'id' => 'ID'
        ));
        $this->addElement('center', true, 'Block', 'Flow', 'Common');
        $this->addElement('dir', true, 'Block', 'Required: li', 'Common', array(
            'compact' => new HTMLPurifier_AttrDef_HTML_Bool('compact')
        ));
        $this->addElement('font', true, 'Inline', 'Inline', array('Core', 'I18N'), array(
            'color' => 'Color',
            'face' => 'Text', // extremely broad, we should
            'size' => 'Text', // tighten it
        ));
        $this->addElement('menu', true, 'Block', 'Required: li', 'Common', array(
            'compact' => new HTMLPurifier_AttrDef_HTML_Bool('compact')
        ));
        $this->addElement('s', true, 'Inline', 'Inline', 'Common');
        $this->addElement('strike', true, 'Inline', 'Inline', 'Common');
        $this->addElement('u', true, 'Inline', 'Inline', 'Common');
        
        // setup modifications to old elements
        
        $li =& $this->addBlankElement('li');
        $li->attr['value'] = new HTMLPurifier_AttrDef_Integer();
        
        $ol =& $this->addBlankElement('ol');
        $ol->attr['start'] = new HTMLPurifier_AttrDef_Integer();
        
        $align = new HTMLPurifier_AttrDef_Enum(array('left', 'right', 'center', 'justify'));
        
        $address =& $this->addBlankElement('address');
        $address->content_model = 'Inline | #PCDATA | p';
        $address->content_model_type = 'optional';
        $address->child = false;
        
        $blockquote =& $this->addBlankElement('blockquote');
        $blockquote->content_model = 'Flow | #PCDATA';
        $blockquote->content_model_type = 'optional';
        $blockquote->child = false;
        
        $br =& $this->addBlankElement('br');
        $br->attr['clear'] = new HTMLPurifier_AttrDef_Enum(array('left', 'all', 'right', 'none'));
        
        $caption =& $this->addBlankElement('caption');
        $caption->attr['align'] = new HTMLPurifier_AttrDef_Enum(array('top', 'bottom', 'left', 'right'));
        
        $div =& $this->addBlankElement('div');
        $div->attr['align'] = $align;
        
        // dl.compact omitted
        
        for ($i = 1; $i <= 6; $i++) {
            $h =& $this->addBlankElement("h$i");
            $h->attr['align'] = $align;
        }
        
        // to be continued...
        
    }
    
}

?>