<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/Generator.php';

class HTMLPurifier_Strategy_RemoveForeignElements extends HTMLPurifier_Strategy
{
    
    var $generator;
    var $definition;
    
    function HTMLPurifier_Definition() {
        $this->generator = new HTMLPurifier_Generator();
        $this->definition = HTMLPurifier_Definition::instance();
    }
    
    function execute($tokens) {
        $result = array();
        foreach($tokens as $token) {
            if (!empty( $token->is_tag )) {
                if (!isset($this->definition->info[$token->name])) {
                    // invalid tag, generate HTML and insert in
                    $token = new HTMLPurifier_Token_Text(
                        $this->generator->generateFromToken($token)
                    );
                }
            } elseif ($token->type == 'comment') {
                // strip comments
                continue;
            } elseif ($token->type == 'text') {
            } else {
                continue;
            }
            $result[] = $token;
        }
        return $result;
    }
    
}

?>