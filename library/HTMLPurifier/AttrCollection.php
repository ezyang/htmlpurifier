<?php

require_once 'HTMLPurifier/AttrTypes.php';
require_once 'HTMLPurifier/AttrDef/Lang.php';

/**
 * Defines common attribute collections that modules reference
 */

class HTMLPurifier_AttrCollection
{
    
    var $info = array(
        'Core' => array(
            // 'xml:space' => false,
            'class' => 'NMTOKENS',
            'id' => 'ID',
            'title' => 'CDATA',
        ),
        'I18N' => array(
            'xml:lang' => false, // see constructor
            'lang' => false, // see constructor
        ),
        'Events' => array(),
        'Style' => array(),
        'Common' => array(
            0 => array('Core', 'Events', 'I18N', 'Style')
        )
    );
    
    function HTMLPurifier_AttrCollection() {
        // setup direct objects
        $this->info['I18N']['xml:lang'] =
        $this->info['I18N']['lang'] = new HTMLPurifier_AttrDef_Lang();
    }
    
    function setup($attr_types, $modules) {
        $info =& $this->info;
        
        // replace string identifiers with actual attribute objects
        foreach ($info as $collection_i => $collection) {
            foreach ($collection as $attr_i => $attr) {
                if ($attr_i === 0) continue;
                if (!is_string($attr)) continue;
                if (isset($attr_types->info[$attr])) {
                    $info[$collection_i][$attr_i] = $attr_types->info[$attr];
                } else {
                    unset($info[$collection_i][$attr_i]);
                }
            }
        }
        
        // merge attribute collections that include others
        foreach ($info as $name => $attr) {
            $this->performInclusions($info[$name]);
        }
        
    }
    
    function performInclusions(&$attr) {
        if (!isset($attr[0])) return;
        $merge = $attr[0];
        // loop through all the inclusions
        for ($i = 0; isset($merge[$i]); $i++) {
            // foreach attribute of the inclusion, copy it over
            foreach ($this->info[$merge[$i]] as $key => $value) {
                if (isset($attr[$key])) continue; // also catches more inclusions
                $attr[$key] = $value;
            }
            if (isset($info[$merge[$i]][0])) {
                // recursion
                $merge = array_merge($merge, isset($info[$merge[$i]][0]));
            }
        }
        unset($attr[0]);
    }
    
}

?>