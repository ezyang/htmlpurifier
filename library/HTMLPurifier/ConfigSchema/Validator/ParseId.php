<?php

/**
 * Parses ID into NAMESPACE and, if appropriate, DIRECTIVE. Expects ID to exist.
 */
class HTMLPurifier_ConfigSchema_Validator_ParseId extends HTMLPurifier_ConfigSchema_Validator
{
    
    public function validate(&$arr, $interchange) {
        $r = explode('.', $arr['ID'], 2);
        $arr['_NAMESPACE'] = $r[0];
        if (isset($r[1])) $arr['_DIRECTIVE'] = $r[1];
    }
    
}
