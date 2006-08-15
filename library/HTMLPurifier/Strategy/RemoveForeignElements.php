<?php

require_once 'HTMLPurifier/Strategy.php';
require_once 'HTMLPurifier/HTMLDefinition.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/TagTransform.php';

/**
 * Removes all unrecognized tags from the list of tokens.
 * 
 * This strategy iterates through all the tokens and removes unrecognized
 * tokens. If a token is not recognized but a TagTransform is defined for
 * that element, the element will be transformed accordingly.
 */

class HTMLPurifier_Strategy_RemoveForeignElements extends HTMLPurifier_Strategy
{
    
    var $generator;
    var $definition;
    
    function HTMLPurifier_Strategy_RemoveForeignElements() {
        $this->generator = new HTMLPurifier_Generator();
        $this->definition = HTMLPurifier_HTMLDefinition::instance();
    }
    
    function execute($tokens, $config) {
        $result = array();
        foreach($tokens as $token) {
            if (!empty( $token->is_tag )) {
                // DEFINITION CALL
                if (isset($this->definition->info[$token->name])) {
                    // leave untouched
                } elseif (
                    isset($this->definition->info_tag_transform[$token->name])
                ) {
                    // there is a transformation for this tag
                    // DEFINITION CALL
                    $token = $this->
                                definition->
                                    info_tag_transform[$token->name]->
                                        transform($token);
                } else {
                    // invalid tag, generate HTML and insert in
                    $token = new HTMLPurifier_Token_Text(
                        $this->generator->generateFromToken($token, $config)
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