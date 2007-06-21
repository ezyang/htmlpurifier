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
     * @param HTMLPurifier_Token Token that caused error
     * @param array Tokens surrounding the offending token above, use true as placeholder
     */
    function send($msg, $token, $context_tokens = array(true)) {
        $this->errors[] = array($msg, $token, $context_tokens);
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
    function getHTMLFormatted($config) {
        $generator = new HTMLPurifier_Generator();
        $context = new HTMLPurifier_Context();
        $generator->generateFromTokens(array(), $config, $context); // initialize
        $ret = array();
        
        $errors = $this->errors;
        
        // sort error array by line
        if ($config->get('Core', 'MaintainLineNumbers')) {
            $lines  = array();
            foreach ($errors as $error) $lines[] = $error[1]->line;
            array_multisort($lines, SORT_ASC, $errors);
        }
        
        foreach ($errors as $error) {
            $string = $generator->escape($error[0]); // message
            if (!empty($error[1]->line)) {
                $string .= ' at line ' . $error[1]->line;
            }
            $string .= ' (<code>';
            foreach ($error[2] as $token) {
                if ($token !== true) {
                    $string .= $generator->escape($generator->generateFromToken($token));
                } else {
                    $string .= '<strong>' . $generator->escape($generator->generateFromToken($error[1])) . '</strong>';
                }
            }
            $string .= '</code>)';
            $ret[] = $string;
        }
        return $ret;
    }
    
}

?>