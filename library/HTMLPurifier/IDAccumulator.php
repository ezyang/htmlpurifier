<?php

class HTMLPurifier_IDAccumulator
{
    
    var $ids = array();
    
    function &instance() {
        static $instance = null;
        if (empty($instance)) {
            $instance = new HTMLPurifier_IDAccumulator();
        }
        return $instance;
    }
    
    function add($id) {
        if (isset($this->ids[$id])) return false;
        return $this->ids[$id] = true;
    }
    
    function reset() {
        $this->ids = array();
    }
    
}

?>