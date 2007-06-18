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
     * Has the language object been loaded yet?
     * @private
     */
    var $_loaded = false;
    
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
     * Formats a localised message with passed parameters
     * @param $key string identifier of message
     * @param $param Parameter to substitute in (arbitrary number)
     * @return string localised message
     */
    function formatMessage($key) {
        if (!$this->_loaded) $this->load();
        if (!isset($this->messages[$key])) return "[$key]";
        $raw = $this->messages[$key];
        $args = func_get_args();
        $substitutions = array();
        for ($i = 1; $i < count($args); $i++) {
            $substitutions['$' . $i] = $args[$i];
        }
        return strtr($raw, $substitutions);
    }
    
}

?>