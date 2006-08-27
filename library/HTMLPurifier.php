<?php

/*!
 * @mainpage
 * 
 * HTMLPurifier is an HTML filter that will take an arbitrary snippet of
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
 * However, most users will only need to interface with the HTMLPurifier
 * class, so this massive amount of infrastructure is usually concealed.
 * If you plan on working with the internals, be sure to include
 * HTMLPurifier_ConfigDef and HTMLPurifier_Config.
 */

require_once 'HTMLPurifier/ConfigDef.php';
require_once 'HTMLPurifier/Config.php';
require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/HTMLDefinition.php';
require_once 'HTMLPurifier/Generator.php';
require_once 'HTMLPurifier/Strategy/Core.php';

HTMLPurifier_ConfigDef::define(
    'Core', 'Encoding', 'utf-8', 'istring',
    'Defines the input and output character encodings to use. HTMLPurifier '.
    'internally uses UTF-8, making that the painless default choice. Note '.
    'certain implementations of HTMLPurifier_Lexer are intelligent enough '.
    'automatically detect encoding, however, output format will always be '.
    'this value.'
);
HTMLPurifier_ConfigDef::defineAllowedValues(
    'Core', 'Encoding', array(
        'utf-8',
        'iso-8859-1'
    )
);
HTMLPurifier_ConfigDef::defineValueAliases(
    'Core', 'Encoding', array(
        'iso8859-1' => 'iso-8859-1'
    )
);

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
    
    var $lexer, $strategy, $generator;
    
    /**
     * Initializes the purifier.
     * @param $config Optional HTMLPurifier_Config object for all instances of
     *                the purifier, if omitted, a default configuration is
     *                supplied.
     */
    function HTMLPurifier($config = null) {
        $this->config = $config ? $config : HTMLPurifier_Config::createDefault();
        
        $this->lexer = HTMLPurifier_Lexer::create();
        $this->strategy = new HTMLPurifier_Strategy_Core();
        $this->generator = new HTMLPurifier_Generator();
    }
    
    /**
     * Filters an HTML snippet/document to be XSS-free and standards-compliant.
     * 
     * @param $html String of HTML to purify
     * @param $config HTMLPurifier_Config object for this operation, if omitted,
     *                defaults to the config object specified during this
     *                object's construction.
     * @return Purified HTML
     */
    function purify($html, $config = null) {
        $config = $config ? $config : $this->config;
        return
            $this->generator->generateFromTokens(
                $this->strategy->execute(
                    $this->lexer->tokenizeHTML($html, $config),
                $config
            ),
            $config
        );
    }
    
}

?>