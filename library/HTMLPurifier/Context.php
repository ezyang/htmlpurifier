<?php

/**
 * Registry object that contains information about the current context.
 */
class HTMLPurifier_Context
{
    
    /**
     * Private array that stores the references.
     * @private
     */
    var $_storage = array();
    
    /**
     * Registers a variable into the context.
     * @param $name String name
     * @param $ref Variable to be registered
     */
    function register($name, &$ref) {
        if (isset($this->_storage[$name])) {
            trigger_error('Name collision, cannot re-register',
                          E_USER_ERROR);
            return;
        }
        $this->_storage[$name] =& $ref;
    }
    
    /**
     * Retrieves a variable reference from the context.
     * @param $name String name
     */
    function &get($name) {
        if (!isset($this->_storage[$name])) {
            trigger_error('Attempted to retrieve non-existent variable',
                          E_USER_ERROR);
            return;
        }
        return $this->_storage[$name];
    }
    
    /**
     * Destorys a variable in the context.
     * @param $name String name
     */
    function destroy($name) {
        if (!isset($this->_storage[$name])) {
            trigger_error('Attempted to destroy non-existent variable',
                          E_USER_ERROR);
            return;
        }
        unset($this->_storage[$name]);
    }
    
}

?>