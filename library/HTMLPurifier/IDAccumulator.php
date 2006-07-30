<?php

class HTMLPurifier_IDAccumulator
{
    
    var $ids = array();
    
    function add($id) {
        if (isset($this->ids[$id])) return false;
        return $this->ids[$id] = true;
    }
    
    function load($array_of_ids) {
        foreach ($array_of_ids as $id) {
            $this->ids[$id] = true;
        }
    }
    
}

?>