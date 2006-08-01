<?php

require_once 'HTMLPurifier/AttrDef.php';
require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_AttrDef_ID extends HTMLPurifier_AttrDef
{
    
    function validate($id, &$accumulator) {
        
        $id = @ (string) $id; // sanity check
        
        if ($id === '') return false;
        if (isset($accumulator->ids[$id])) return false;
        
        // we purposely avoid using regex, hopefully this is faster
        
        if (ctype_alpha($id)) {
            $result = true;
        } else {
            if (!ctype_alpha(@$id[0])) return false;
            $trim = trim(
                $id,
                'A..Za..z0..9:-._'
              );
            $result = ($trim === '');
        }
        
        if ($result) $accumulator->add($id);
        
        return $result;
        
    }
    
}

?>