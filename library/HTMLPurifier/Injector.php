<?php

/**
 * Injects tokens into the document while parsing for well-formedness.
 * This enables "formatter-like" functionality such as auto-paragraphing,
 * smiley-ification and linkification to take place.
 */
class HTMLPurifier_Injector
{
    
    /**
     * Amount of tokens the injector needs to skip + 1. Because
     * the decrement is the first thing that happens, this needs to
     * be one greater than the "real" skip count.
     */
    var $skip = 1;
    
    /**
     * Instance of HTMLPurifier_HTMLDefinition
     */
    var $htmlDefinition;
    
    /**
     * Reference to CurrentNesting variable in Context. This is an array
     * list of tokens that we are currently "inside"
     */
    var $currentNesting;
    
    /**
     * Reference to InputTokens variable in Context. This is an array
     * list of the input tokens that are being processed.
     */
    var $inputTokens;
    
    /**
     * Reference to InputIndex variable in Context. This is an integer
     * array index for $this->inputTokens that indicates what token
     * is currently being processed.
     */
    var $inputIndex;
    
    /**
     * Prepares the injector by giving it the config and context objects,
     * so that important variables can be extracted and not passed via
     * parameter constantly. Remember: always instantiate a new injector
     * when handling a set of HTML.
     */
    function prepare($config, &$context) {
        $this->htmlDefinition = $config->getHTMLDefinition();
        $this->currentNesting =& $context->get('CurrentNesting');
        $this->inputTokens    =& $context->get('InputTokens');
        $this->inputIndex     =& $context->get('InputIndex');
    }
    
    /**
     * Tests if the context node allows a certain element
     * @param $name Name of element to test for
     * @return True if element is allowed, false if it is not
     */
    function allowsElement($name) {
        if (!empty($this->currentNesting)) {
            $parent_token = array_pop($this->currentNesting);
            $this->currentNesting[] = $parent_token;
            $parent = $this->htmlDefinition->info[$parent_token->name];
        } else {
            $parent = $this->htmlDefinition->info_parent_def;
        }
        if (!isset($parent->child->elements[$name]) || isset($parent->excludes[$name])) {
            return false;
        }
        return true;
    }
    
    /**
     * Handler that is called when a text token is processed
     */
    function handleText(&$token) {}
    
    /**
     * Handler that is called when a start token is processed
     */
    function handleStart(&$token) {}
    
}

?>