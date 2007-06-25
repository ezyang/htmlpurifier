<?php

require_once 'HTMLPurifier/Generator.php';

/**
 * Error collection class that enables HTML Purifier to report HTML
 * problems back to the user
 */
class HTMLPurifier_ErrorCollector
{
    
    var $errors = array();
    var $locale;
    var $context;
    
    function HTMLPurifier_ErrorCollector(&$context) {
        $this->locale  =& $context->get('Locale');
        $this->context =& $context;
    }
    
    /**
     * Sends an error message to the collector for later use
     * @param $line Integer line number, or HTMLPurifier_Token that caused error
     * @param $severity int Error severity, PHP error style (don't use E_USER_)
     * @param $msg string Error message text
     */
    function send($severity, $msg, $args = array()) {
        if (func_num_args() == 2) {
            $msg = $this->locale->getMessage($msg);
        } else {
            // setup one-based array if necessary
            if (!is_array($args)) {
                $args = func_get_args();
                array_shift($args);
                unset($args[0]);
            }
            $msg = $this->locale->formatMessage($msg, $args);
        }
        
        $token = $this->context->get('CurrentToken', true);
        $line  = $token ? $token->line : $this->context->get('CurrentLine', true);
        $attr  = $this->context->get('CurrentAttr', true);
        
        $this->errors[] = array($line, $severity, $msg);
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
        $generator->generateFromTokens(array(), $config, $this->context); // initialize
        $ret = array();
        
        $errors = $this->errors;
        
        // sort error array by line
        if ($config->get('Core', 'MaintainLineNumbers')) {
            $lines  = array();
            foreach ($errors as $error) {
                $lines[] = $error[0];
            }
            array_multisort($lines, SORT_ASC, $errors);
        }
        
        foreach ($errors as $error) {
            list($line, $severity, $msg) = $error;
            $string = '';
            $string .= $this->locale->getErrorName($severity) . ': ';
            $string .= $generator->escape($msg); 
            if ($line) {
                $string .= $this->locale->formatMessage(
                    'ErrorCollector: At line', array('line' => $line));
            }
            $ret[] = $string;
        }
        
        if (empty($errors)) {
            return '<p>' . $this->locale->getMessage('ErrorCollector: No errors') . '</p>';
        } else {
            return '<ul><li>' . implode('</li><li>', $ret) . '</li></ul>';
        }
        
    }
    
}

?>