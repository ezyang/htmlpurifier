<?php

require_once 'HTMLPurifier/Generator.php';

/**
 * Error collection class that enables HTML Purifier to report HTML
 * problems back to the user
 */
class HTMLPurifier_ErrorCollector
{
    
    var $errors = array();
    
    /**
     * Sends an error message to the collector for later use
     * @param string Error message text
     * @param int Error severity, PHP error style (don't use E_USER_)
     * @param HTMLPurifier_Token Token that caused error
     * @param array Tokens surrounding the offending token above, use true as placeholder
     */
    function send($msg, $severity, $token, $context_tokens = array(true)) {
        $this->errors[] = array($msg, $severity, $token, $context_tokens);
    }
    
    /**
     * Retrieves raw error data for custom formatter to use
     * @param List of arrays in format of array(Error message text,
     *        token that caused error, tokens surrounding token)
     */
    function getRaw() {
        return $this->errors;
    }
    
    /**
     * Default HTML formatting implementation for error messages
     * @param $config Configuration array, vital for HTML output nature
     */
    function getHTMLFormatted($config, &$context) {
        $generator = new HTMLPurifier_Generator();
        $generator->generateFromTokens(array(), $config, $context); // initialize
        $ret = array();
        
        $errors = $this->errors;
        $locale = $context->get('Locale');
        
        // sort error array by line
        if ($config->get('Core', 'MaintainLineNumbers')) {
            $lines  = array();
            foreach ($errors as $error) $lines[] = $error[2]->line;
            array_multisort($lines, SORT_ASC, $errors);
        }
        
        foreach ($errors as $error) {
            list($msg, $severity, $token, $context_tokens) = $error;
            $string = '';
            $string .= $locale->getErrorName($severity) . ': ';
            $string .= $generator->escape($msg); 
            if (!empty($token->line)) {
                $string .= ' at line ' . $token->line;
            }
            $string .= ' (<code>';
            foreach ($context_tokens as $context_token) {
                if ($context_token !== true) {
                    $string .= $generator->escape($generator->generateFromToken($context_token));
                } else {
                    $string .= '<strong>' . $generator->escape($generator->generateFromToken($token)) . '</strong>';
                }
            }
            $string .= '</code>)';
            $ret[] = $string;
        }
        return $ret;
    }
    
}

?>