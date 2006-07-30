<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_Strategy_ValidateAttributes extends HTMLPurifier_Strategy
{
    
    var $definition;
    
    function HTMLPurifier_Strategy_ValidateAttributes() {
        $this->definition = HTMLPurifier_Definition::instance();
    }
    
    function execute($tokens) {
        $accumulator = new HTMLPurifier_IDAccumulator();
        $d_defs = $this->definition->info['attr']['*'];
        foreach ($tokens as $key => $token) {
            if ($token->type !== 'start' && $token->type !== 'end') continue;
            $name = $token->name;
            $attr = $token->attributes;
            $defs = isset($this->definition->info['attr'][$name]) ?
                $this->definition->attr[$name] : array();
            $changed = false;
            foreach ($attr as $attr_key => $value) {
                if ( isset($defs[$attr_key]) ) {
                    if (!$defs[$attr_key]) {
                        $result = false;
                    } else {
                        $result = $defs[$attr_key]->validate($value, $accumulator);
                    }
                } elseif ( isset($d_defs[$attr_key]) ) {
                    $result = $d_defs[$attr_key]->validate($value, $accumulator);
                } else {
                    $result = false;
                }
                if (!$result) {
                    $changed = true;
                    unset($attr[$attr_key]);
                }
            }
            if ($changed) {
                $tokens[$key]->attributes = $attr;
            }
        }
        return $tokens;
    }
    
}

?>