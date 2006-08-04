<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_AttrDef_ID extends HTMLPurifier_AttrDef
{
    
    function validate($old_id, $config, &$accumulator) {
        
        $id = trim($old_id); // trim it first
        
        if ($id === '') return false;
        if (isset($accumulator->ids[$id])) return false;
        
        // we purposely avoid using regex, hopefully this is faster
        
        if (ctype_alpha($id)) {
            $result = true;
        } else {
            if (!ctype_alpha(@$id[0])) return false;
            $trim = trim( // primitive style of regexps, I suppose
                $id,
                'A..Za..z0..9:-._'
              );
            $result = ($trim === '');
        }
        
        if ($result) $accumulator->add($id);
        
        // if no change was made to the ID, return the result
        // else, return the new id if stripping whitespace made it
        //     valid, or return false.
        return ($id == $old_id) ? $result : ($result ? $id : false);
        
    }
    
}

?>