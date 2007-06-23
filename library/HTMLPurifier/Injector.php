<?php

/**
 * Injects tokens into the document while parsing for well-formedness.
 * This enables "formatter-like" functionality such as auto-paragraphing,
 * smiley-ification and linkification to take place.
 */
class HTMLPurifier_Injector
{
    
    /**
     * Handler that is called when a text token is processed
     */
    function handleText(&$token, $config, &$context) {}
    
    /**
     * Handler that is called when a start token is processed
     */
    function handleStart(&$token, $config, &$context) {}
    
}

?>