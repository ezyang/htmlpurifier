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
    var $generator;
    var $context;
    
    function HTMLPurifier_ErrorCollector(&$context) {
        $this->locale  =& $context->get('Locale');
        $this->generator =& $context->get('Generator');
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
        // perform special substitutions
        // Currently defined: $CurrentToken.Name, $CurrentToken.Serialized,
        //     $CurrentAttr.Name, $CurrentAttr.Value
        if (strpos($msg, '$') !== false) {
            $subst = array();
            if (!is_null($token)) {
                if (isset($token->name)) $subst['$CurrentToken.Name'] = $token->name;
                $subst['$CurrentToken.Serialized'] = $this->generator->generateFromToken($token);
            }
            if (!is_null($attr)) {
                $subst['$CurrentAttr.Name'] = $attr;
                if (isset($token->attr[$attr])) $subst['$CurrentAttr.Value'] = $token->attr[$attr];
            }
            if (!empty($subst)) $msg = strtr($msg, $subst);
        }
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
        // line numbers are enabled if they aren't explicitly disabled
        if ($config->get('Core', 'MaintainLineNumbers') !== false) {
            $lines  = array();
            $original_order = array();
            foreach ($errors as $i => $error) {
                $lines[] = $error[0];
                $original_order[] = $i;
            }
            array_multisort($lines, SORT_ASC, $original_order, SORT_ASC, $errors);
        }
        
        foreach ($errors as $error) {
            list($line, $severity, $msg) = $error;
            $string = '';
            $string .= $this->locale->getErrorName($severity) . ': ';
            $string .= $generator->escape($msg); 
            if ($line) {
                // have javascript link generation that causes 
                // textarea to skip to the specified line
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