<?php

require_once 'HTMLPurifier/LanguageFactory.php';

class HTMLPurifier_Language
{
    
    /**
     * ISO 639 language code of language. Prefers shortest possible version
     */
    var $code = 'en';
    
    /**
     * Fallback language code
     */
    var $fallback = false;
    
    /**
     * Array of localizable messages
     */
    var $messages = array();
    
    /**
     * Array of localizable error codes
     */
    var $errorNames = array();
    
    /**
     * Has the language object been loaded yet?
     * @private
     */
    var $_loaded = false;
    
    /**
     * Instances of HTMLPurifier_Config and HTMLPurifier_Context
     */
    var $config, $context;
    
    function HTMLPurifier_Language($config, &$context) {
        $this->config  = $config;
        $this->context =& $context;
    }
    
    /**
     * Loads language object with necessary info from factory cache
     * @note This is a lazy loader
     */
    function load() {
        if ($this->_loaded) return;
        $factory = HTMLPurifier_LanguageFactory::instance();
        $factory->loadLanguage($this->code);
        foreach ($factory->keys as $key) {
            $this->$key = $factory->cache[$this->code][$key];
        }
        $this->_loaded = true;
    }
    
    /**
     * Retrieves a localised message.
     * @param $key string identifier of message
     * @return string localised message
     */
    function getMessage($key) {
        if (!$this->_loaded) $this->load();
        if (!isset($this->messages[$key])) return "[$key]";
        return $this->messages[$key];
    }
    
    /**
     * Retrieves a localised error name.
     * @param $int integer error number, corresponding to PHP's error
     *             reporting
     * @return string localised message
     */
    function getErrorName($int) {
        if (!$this->_loaded) $this->load();
        if (!isset($this->errorNames[$int])) return "[Error: $int]";
        return $this->errorNames[$int];
    }
    
    /**
     * Formats a localised message with passed parameters
     * @param $key string identifier of message
     * @param $args Parameters to substitute in
     * @return string localised message
     * @todo Implement conditionals? Right now, some messages make
     *     reference to line numbers, but those aren't always available
     */
    function formatMessage($key, $args = array()) {
        if (!$this->_loaded) $this->load();
        if (!isset($this->messages[$key])) return "[$key]";
        $raw = $this->messages[$key];
        $subst = array();
        $generator = false;
        foreach ($args as $i => $value) {
            if (is_object($value)) {
                // complicated stuff
                if (!$generator) $generator = $this->context->get('Generator');
                // assuming it's a token
                if (isset($value->name)) $subst['$'.$i.'.Name'] = $value->name;
                if (isset($value->data)) $subst['$'.$i.'.Data'] = $value->data;
                $subst['$'.$i.'.Compact'] = 
                $subst['$'.$i.'.Serialized'] = $generator->generateFromToken($value);
                // a more complex algorithm for compact representation
                // could be introduced for all types of tokens. This
                // may need to be factored out into a dedicated class
                if (!empty($value->attr)) {
                    $stripped_token = $value->copy();
                    $stripped_token->attr = array();
                    $subst['$'.$i.'.Compact'] = $generator->generateFromToken($stripped_token);
                }
                $subst['$'.$i.'.Line'] = $value->line ? $value->line : 'unknown';
                continue;
            }
            $subst['$' . $i] = $value;
        }
        return strtr($raw, $subst);
    }
    
}

?>