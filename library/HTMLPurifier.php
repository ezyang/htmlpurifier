<?php

/*!
 * @mainpage
 * 
 * HTMLPurifier is a purification class that will take an arbitrary snippet of
 * HTML and rigorously test, validate and filter it into a version that
 * is safe for output onto webpages. It achieves this by:
 * 
 *  -# Lexing (parsing into tokens) the document,
 *  -# Removing all elements not in the whitelist,
 *  -# Making the tokens well-formed,
 *  -# Fixing the nesting of the nodes,
 *  -# Validating attributes of the nodes, and
 *  -# Generating HTML from the purified tokens.
 * 
 * See /docs/spec.txt for more details.
 */

require_once 'HTMLPurifier/Lexer.php';
require_once 'HTMLPurifier/Definition.php';
require_once 'HTMLPurifier/Generator.php';

/**
 * Main library execution class.
 * 
 * Facade that performs calls to the HTMLPurifier_Lexer,
 * HTMLPurifier_Definition and HTMLPurifier_Generator subsystems in order to
 * purify HTML.
 */
class HTMLPurifier
{
    
    var $lexer;         /*!< @brief Instance of HTMLPurifier_Lexer concrete
                                    implementation. */
    var $definition;    /*!< @brief Instance of HTMLPurifier_Definition. */
    var $generator;     /*!< @brief Instance of HTMLPurifier_Generator. */
    
    /**
     * Initializes the purifier.
     * 
     * The constructor instantiates all necessary sub-objects to do the job,
     * because creating some of them (esp. HTMLPurifier_Definition) can be
     * expensive.
     * 
     * @todo Accept Policy object to define configuration.
     */
    function HTMLPurifier() {
        $this->lexer        = new HTMLPurifier_Lexer::create();
        $this->definition   = new HTMLPurifier_Definition();
        $this->generator    = new HTMLPurifier_Generator();
    }
    
    /**
     * Purifies HTML.
     * 
     * @param $html String of HTML to purify
     * @return Purified HTML
     */
    function purify($html) {
        $tokens = $this->lexer->tokenizeHTML($html);
        $tokens = $this->definition->purifyTokens($tokens);
        return $this->generator->generateFromTokens($tokens);
    }
    
}

?>