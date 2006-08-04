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
    
    function execute($tokens, $config = null) {
        
        // load default configuration object if none passed
        if (!$config) $config = HTMLPurifier_Config::createDefault();
        
        // setup ID accumulator and load it with blacklisted IDs
        $accumulator = new HTMLPurifier_IDAccumulator();
        $accumulator->load($config->attr_id_blacklist);
        
        // DEFINITION CALL
        $d_defs = $this->definition->info_global_attr;
        
        foreach ($tokens as $key => $token) {
            if ($token->type !== 'start' && $token->type !== 'end') continue;
            
            // DEFINITION CALL
            $defs = $this->definition->info[$token->name]->attr;
            
            $attr = $token->attributes;
            $changed = false;
            foreach ($attr as $attr_key => $value) {
                
                // call the definition
                if ( isset($defs[$attr_key]) ) {
                    if (!$defs[$attr_key]) {
                        $result = false;
                    } else {
                        $result = $defs[$attr_key]->validate($value, $config, $accumulator);
                    }
                } elseif ( isset($d_defs[$attr_key]) ) {
                    $result = $d_defs[$attr_key]->validate($value, $config, $accumulator);
                } else {
                    $result = false;
                }
                
                // put the results into effect
                if ($result === false || $result === null) {
                    $changed = true;
                    unset($attr[$attr_key]);
                } elseif (is_string($result)) {
                    // simple substitution
                    $changed = true;
                    $attr[$attr_key] = $result;
                }
                // we'd also want slightly more complicated substitution,
                // although we're not sure how colliding attributes would
                // resolve
            }
            if ($changed) {
                $tokens[$key]->attributes = $attr;
            }
        }
        return $tokens;
    }
    
}

?>