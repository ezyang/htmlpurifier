<?php

/**
 * Parses DEFAULT into _DEFAULT. Expects DEFAULT, _TYPE, _NULL and ID to exist.
 */
class HTMLPurifier_ConfigSchema_Validator_ParseDefault extends HTMLPurifier_ConfigSchema_Validator
{
    
    public function validate(&$arr, $interchange) {
        $parser = new HTMLPurifier_VarParser_Native(); // not configurable yet
        try {
            $arr['_DEFAULT'] = $parser->parse($arr['DEFAULT'], $arr['_TYPE'], $arr['_NULL']);
        } catch (HTMLPurifier_VarParserException $e) {
            throw new HTMLPurifier_ConfigSchema_Exception('Invalid type for default value in '. $arr['ID'] .': ' . $e->getMessage());
        }
    }
    
}
