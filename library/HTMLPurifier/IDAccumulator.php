<?php

class HTMLPurifier_IDAccumulator
{
    
    var $ids = array();
    
    function add($id) {
        if (isset($this->ids[$id])) return false;
        return $this->ids[$id] = true;
    }
    
}

?>