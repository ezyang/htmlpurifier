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
            0 => array('Style'),
            // 'xml:space' => false,
            'class' => 'NMTOKENS',
            'id' => 'ID',
            'title' => 'CDATA',
        ),
        'I18N' => array(
            'xml:lang' => false, // see constructor
            'lang' => false, // see constructor
        ),
        'Common' => array(
            0 => array('Core', 'I18N')
        )
    );
    
    function HTMLPurifier_AttrCollection() {
        // setup direct objects
        $this->info['I18N']['xml:lang'] =
        $this->info['I18N']['lang'] = new HTMLPurifier_AttrDef_Lang();
    }
    
    function setup($attr_types, $modules) {
        $info =& $this->info;
        foreach ($modules as $module) {
            foreach ($module->attr_collection as $coll_i => $coll) {
                foreach ($coll as $attr_i => $attr) {
                    if ($attr_i === 0) {
                        // merge in includes
                        $info[$coll_i][$attr_i] = array_merge(
                            $info[$coll_i][$attr_i], $attr);
                        continue;
                    }
                    $info[$coll_i][$attr_i] = $attr;
                }
            }
        }
        foreach ($info as $name => $attr) {
            // merge attribute collections that include others
            $this->performInclusions($info[$name]);
            // replace string identifiers with actual attribute objects
            $this->expandIdentifiers($info[$name], $attr_types);
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
    
    function expandIdentifiers(&$attr, $attr_types) {
        foreach ($attr as $def_i => $def) {
            if ($def_i === 0) continue;
            if (!is_string($def)) continue;
            if (isset($attr_types->info[$def])) {
                $attr[$def_i] = $attr_types->info[$def];
            } else {
                unset($attr[$def_i]);
            }
        }
    }
    
}

?>