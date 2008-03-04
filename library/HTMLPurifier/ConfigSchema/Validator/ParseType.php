<?php

/**
 * Parses TYPE into _TYPE and _NULL. Expects TYPE and ID to exist.
 */
class HTMLPurifier_ConfigSchema_Validator_ParseType extends HTMLPurifier_ConfigSchema_Validator
{
    
    public function validate(&$arr, $interchange) {
        $r = explode('/', $arr['TYPE'], 2);
        if (!isset($interchange->types[$r[0]])) {
            $this->error('Invalid type ' . $r[0] . ' for configuration directive ' . $arr['ID']);
        }
        $arr['_TYPE'] = $r[0];
        $arr['_NULL'] = (isset($r[1]) && $r[1] === 'null');
        
    }
    
}
