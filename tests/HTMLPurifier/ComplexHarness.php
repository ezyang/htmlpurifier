<?php

require_once 'HTMLPurifier/Lexer/DirectLex.php';

/**
 * General-purpose test-harness that makes testing functions that require
 * configuration and context objects easier when those two parameters are
 * meaningless.  See HTMLPurifier_ChildDefTest for a good example of usage.
 */
class HTMLPurifier_ComplexHarness extends HTMLPurifier_Harness
{
    
    /**
     * Instance of the object that will execute the method
     */
    var $obj;
    
    /**
     * Name of the function to be executed
     */
    var $func;
    
    /**
     * Whether or not the method deals in tokens. If set to true, assertResult()
     * will transparently convert HTML to and back from tokens.
     */
    var $to_tokens = false;
    
    /**
     * Whether or not to convert tokens back into HTML before performing
     * equality check, has no effect on bools.
     */
    var $to_html = false;
    
    /**
     * Instance of an HTMLPurifier_Lexer implementation.
     */
    var $lexer;
    
    /**
     * Instance of HTMLPurifier_Generator
     */
    var $generator;
    
    /**
     * Default config to fall back on if no config is available
     */
    var $config;
    
    /**
     * Default context to fall back on if no context is available
     */
    var $context;
    
    function HTMLPurifier_ComplexHarness() {
        $this->lexer     = new HTMLPurifier_Lexer_DirectLex();
        $this->generator = new HTMLPurifier_Generator();
        parent::HTMLPurifier_Harness();
    }
    
    /**
     * Asserts a specific result from a one parameter + config/context function
     * @param $input Input parameter
     * @param $expect Expectation
     * @param $config Configuration array in form of Ns.Directive => Value.
     *                Has no effect if $this->config is set.
     * @param $context_array Context array in form of Key => Value or an actual
     *                       context object.
     */
    function assertResult($input, $expect = true,
        $config_array = array(), $context_array = array()
    ) {
        
        // setup config 
        if ($this->config) {
            $config = HTMLPurifier_Config::create($this->config);
            $config->autoFinalize = false;
            $config->loadArray($config_array);
        } else {
            $config = HTMLPurifier_Config::create($config_array);
        }
        
        // setup context object. Note that we are operating on a copy of it!
        // When necessary, extend the test harness to allow post-tests
        // on the context object
        if (empty($this->context)) {
            $context = new HTMLPurifier_Context();
            $context->loadArray($context_array);
        } else {
            $context =& $this->context;
        }
        
        if ($this->to_tokens && is_string($input)) {
            // $func may cause $input to change, so "clone" another copy
            // to sacrifice
            $input   = $this->lexer->tokenizeHTML($s = $input, $config, $context);
            $input_c = $this->lexer->tokenizeHTML($s, $config, $context);
        } else {
            $input_c = $input;
        }
        
        // call the function
        $func = $this->func;
        $result = $this->obj->$func($input_c, $config, $context);
        
        // test a bool result
        if (is_bool($result)) {
            $this->assertIdentical($expect, $result);
            return;
        } elseif (is_bool($expect)) {
            $expect = $input;
        }
        
        if ($this->to_html) {
            $result = $this->generator->
              generateFromTokens($result, $config, $context);
            if (is_array($expect)) {
                $expect = $this->generator->
                  generateFromTokens($expect, $config, $context);
            }
        }
        
        $this->assertIdentical($expect, $result);
        
    }
    
}


