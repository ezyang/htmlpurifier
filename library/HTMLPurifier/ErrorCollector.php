<?php

/**
 * Error collection class that enables HTML Purifier to report HTML
 * problems back to the user
 */
class HTMLPurifier_ErrorCollector
{
    
    /**
     * Identifiers for the returned error array. These are purposely numeric
     * so list() can be used.
     */
    const LINENO   = 0;
    const SEVERITY = 1;
    const MESSAGE  = 2;
    const CHILDREN = 3;
    
    protected $errors;
    protected $_current;
    protected $_stacks = array(array());
    protected $locale;
    protected $generator;
    protected $context;
    
    public function __construct($context) {
        $this->locale    =& $context->get('Locale');
        $this->context   = $context;
        $this->_current  =& $this->_stacks[0];
        $this->errors    =& $this->_stacks[0];
    }
    
    /**
     * Sends an error message to the collector for later use
     * @param $severity int Error severity, PHP error style (don't use E_USER_)
     * @param $msg string Error message text
     * @param $subst1 string First substitution for $msg
     * @param $subst2 string ...
     */
    public function send($severity, $msg) {
        
        $args = array();
        if (func_num_args() > 2) {
            $args = func_get_args();
            array_shift($args);
            unset($args[0]);
        }
        
        $token = $this->context->get('CurrentToken', true);
        $line  = $token ? $token->line : $this->context->get('CurrentLine', true);
        $attr  = $this->context->get('CurrentAttr', true);
        
        // perform special substitutions, also add custom parameters
        $subst = array();
        if (!is_null($token)) {
            $args['CurrentToken'] = $token;
        }
        if (!is_null($attr)) {
            $subst['$CurrentAttr.Name'] = $attr;
            if (isset($token->attr[$attr])) $subst['$CurrentAttr.Value'] = $token->attr[$attr];
        }
        
        if (empty($args)) {
            $msg = $this->locale->getMessage($msg);
        } else {
            $msg = $this->locale->formatMessage($msg, $args);
        }
        
        if (!empty($subst)) $msg = strtr($msg, $subst);
        
        // (numerically indexed)
        $this->_current[] = array(
            self::LINENO   => $line,
            self::SEVERITY => $severity,
            self::MESSAGE  => $msg,
            self::CHILDREN => array()
        );
    }
    
    /**
     * Begins the collection of a number of sub-errors. This is useful if you
     * are entering a function that may generate errors, but you are able
     * to detect the overall state afterwards.
     */
    public function start() {
        $this->_stacks[] = array();
        $this->_resetCurrent();
    }
    
    /**
     * Terminates the collection of sub-errors, interface is otherwise identical
     * to send(). Any sub-errors will be registered as children (3) to this
     * error.
     * 
     * @param $severity int Error severity
     * @param $msg string Error message text
     * @param $subst1 string First substitution for $msg
     * @param $subst2 string ...
     * 
     * @note If end() is called with no parameters, it is quiet unless there
     *       were sub-errors.
     */
    public function end() {
        $stack = array_pop($this->_stacks);
        $this->_resetCurrent();
        $args = func_get_args();
        if ($args) {
            call_user_func_array(array($this, 'send'), $args);
        } elseif ($stack) {
            $this->send(E_NOTICE, 'ErrorCollector: Incidental errors');
        }
        if ($stack) {
            $this->_current[count($this->_current) - 1][3] = $stack;
        }
    }
    
    /**
     * Resets the _current member variable to the top of the stacks; i.e.
     * the active set of errors being collected.
     */
    protected function _resetCurrent() {
        $this->_current =& $this->_stacks[count($this->_stacks) - 1];
    }
    
    /**
     * Retrieves raw error data for custom formatter to use
     * @param List of arrays in format of array(line of error,
     *        error severity, error message,
     *        recursive sub-errors array)
     */
    public function getRaw() {
        return $this->errors;
    }
    
    /**
     * Default HTML formatting implementation for error messages
     * @param $config Configuration array, vital for HTML output nature
     * @param $errors Errors array to display; used for recursion.
     */
    public function getHTMLFormatted($config, $errors = null) {
        $ret = array();
        
        $generator = new HTMLPurifier_Generator($config, $this->context);
        if ($errors === null) $errors = $this->errors;
        
        // sort error array by line
        // line numbers are enabled if they aren't explicitly disabled
        if ($config->get('Core', 'MaintainLineNumbers') !== false) {
            $has_line       = array();
            $lines          = array();
            $original_order = array();
            foreach ($errors as $i => $error) {
                $has_line[] = (int) (bool) $error[self::LINENO];
                $lines[] = $error[self::LINENO];
                $original_order[] = $i;
            }
            array_multisort($has_line, SORT_DESC, $lines, SORT_ASC, $original_order, SORT_ASC, $errors);
        }
        
        foreach ($errors as $error) {
            list($line, $severity, $msg, $children) = $error;
            $string = '';
            $string .= '<strong>' . $this->locale->getErrorName($severity) . '</strong>: ';
            $string .= $generator->escape($msg); 
            if ($line) {
                // have javascript link generation that causes 
                // textarea to skip to the specified line
                $string .= $this->locale->formatMessage(
                    'ErrorCollector: At line', array('line' => $line));
            }
            if ($children) {
                $string .= $this->getHTMLFormatted($config, $children);
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

