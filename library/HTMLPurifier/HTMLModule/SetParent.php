<?php

HTMLPurifier_ConfigSchema::define(
    'HTML', 'Parent', 'div', 'string',
    'String name of element that HTML fragment passed to library will be '.
    'inserted in.  An interesting variation would be using span as the '.
    'parent element, meaning that only inline tags would be allowed. '.
    'This directive has been available since 1.3.0.'
);

/**
 * Proprietary module that sets up the parent definitions.
 */

class HTMLPurifier_HTMLModule_SetParent extends HTMLPurifier_HTMLModule
{
    
    function postProcess(&$definition) {
        $parent = $definition->config->get('HTML', 'Parent');
        if (isset($definition->info[$parent])) {
            $definition->info_parent = $parent;
        } else {
            trigger_error('Cannot use unrecognized element as parent.',
                E_USER_ERROR);
        }
        $definition->info_parent_def = $definition->info[$definition->info_parent];
    }
    
}

?>