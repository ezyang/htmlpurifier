<?php

require_once 'HTMLPurifier/AttrDef.php';

/**
 * Validates shorthand CSS property list-style.
 * @note This currently does not support list-style-image, as that functionality
 *       is not implemented yet elsewhere.
 */
class HTMLPurifier_AttrDef_ListStyle extends HTMLPurifier_AttrDef
{
    
    /**
     * Local copy of component validators.
     * @note See HTMLPurifier_AttrDef_Font::$info for a similar impl.
     */
    var $info;
    
    function HTMLPurifier_AttrDef_ListStyle($config) {
        $def = $config->getCSSDefinition();
        $this->info['list-style-type']     = $def->info['list-style-type'];
        $this->info['list-style-position'] = $def->info['list-style-position'];
    }
    
    function validate($string, $config, &$context) {
        
        // regular pre-processing
        $string = $this->parseCDATA($string);
        if ($string === '') return false;
        
        $bits = explode(' ', strtolower($string)); // bits to process
        
        $caught_type = false;
        $caught_position = false;
        $caught_none = false; // as in keyword none, which is in all of them
        
        $ret = '';
        
        foreach ($bits as $bit) {
            if ($caught_none && ($caught_type || $caught_position)) break;
            if ($caught_type && $caught_position) break;
            
            if ($bit === '') continue;
            
            if ($bit === 'none') {
                if ($caught_none) continue;
                $caught_none = true;
                $ret .= 'none ';
                continue;
            }
            
            // if we add anymore, roll it into a loop
            
            $r = $this->info['list-style-type']->validate($bit, $config, $context);
            if ($r !== false) {
                if ($caught_type) continue;
                $caught_type = true;
                $ret .= $r . ' ';
                continue;
            }
            
            $r = $this->info['list-style-position']->validate($bit, $config, $context);
            if ($r !== false) {
                if ($caught_position) continue;
                $caught_position = true;
                $ret .= $r . ' ';
                continue;
            }
        }
        
        $ret = rtrim($ret);
        return $ret ? $ret : false;
        
    }
    
}

?>