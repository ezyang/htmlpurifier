<?php

/*!
 * @mainpage
 * 
 * HTMLPurifier is a purification class that will take an arbitrary snippet of
 * HTML and rigorously test, validate and filter it into a version that
 * is safe for output onto webpages. It achieves this by:
 * 
 *  -# Lexing (parsing into tokens) the document,
 *  -# Executing various strategies on the tokens:
 *      -# Removing all elements not in the whitelist,
 *      -# Making the tokens well-formed,
 *      -# Fixing the nesting of the nodes, and
 *      -# Validating attributes of the nodes; and
 *  -# Generating HTML from the purified tokens.
 * 
 * See /docs/spec.txt for more details.
 */

require_once 'HTMLPurifier/ConfigDef.php';
require_once 'HTMLPurifier/Config.php';
require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/HTMLDefinition.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Strategy/Core.php';

/**
 * Main library execution class.
 * 
 * Facade that performs calls to the HTMLPurifier_Lexer,
 * HTMLPurifier_Strategy and HTMLPurifier_Generator subsystems in order to
 * purify HTML.
 */
class HTMLPurifier
{
    
    var $config;
    
    /**
     * Initializes the purifier.
     * @param $config Configuration for all instances of the purifier
     */
    function HTMLPurifier($config = null) {
        $this->config = $config ? $config : HTMLPurifier_Config::createDefault();
    }
    
    /**
     * Purifies HTML.
     * 
     * @param $html String of HTML to purify
     * @param $config HTMLPurifier_Config object for this specific round
     * @return Purified HTML
     */
    function purify($html, $config = null) {
        $config = $config ? $config : $this->config;
        $lexer = HTMLPurifier_Lexer::create();
        $strategy = new HTMLPurifier_Strategy_Core();
        $generator = new HTMLPurifier_Generator();
        return $generator->generateFromTokens(
            $strategy->execute(
                $lexer->tokenizeHTML($html), $config
            )
        );
    }
    
}

?>