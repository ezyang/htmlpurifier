<?php

HTMLPurifier_ConfigSchema::define(
    'HTML', 'AllowedElements', null, 'lookup/null',
    'If HTML Purifier\'s tag set is unsatisfactory for your needs, you '.
    'can overload it with your own list of tags to allow.  Note that this '.
    'method is subtractive: it does its job by taking away from HTML Purifier '.
    'usual feature set, so you cannot add a tag that HTML Purifier never '.
    'supported in the first place (like embed, form or head).  If you change this, you '.
    'probably also want to change %HTML.AllowedAttributes. '.
    '<strong>Warning:</strong> If another directive conflicts with the '.
    'elements here, <em>that</em> directive will win and override. '.
    'This directive has been available since 1.3.0.'
);

HTMLPurifier_ConfigSchema::define(
    'HTML', 'AllowedAttributes', null, 'lookup/null',
    'IF HTML Purifier\'s attribute set is unsatisfactory, overload it! '.
    'The syntax is \'tag.attr\' or \'*.attr\' for the global attributes '.
    '(style, id, class, dir, lang, xml:lang).'.
    '<strong>Warning:</strong> If another directive conflicts with the '.
    'elements here, <em>that</em> directive will win and override. For '.
    'example, %HTML.EnableAttrID will take precedence over *.id in this '.
    'directive.  You must set that directive to true before you can use '.
    'IDs at all. This directive has been available since 1.3.0.'
);

/**
 * Proprietary module that further narrows down allowed elements and
 * attributes that were allowed to a user-defined whitelist.
 * @warning This module cannot ADD elements or attributes, you must
 *          implement full definitions yourself!
 */

class HTMLPurifier_HTMLModule_TweakSubtractiveWhitelist extends HTMLPurifier_HTMLModule
{
    
    var $name = 'TweakSubtractiveWhitelist';
    
    function postProcess(&$definition) {
        
        // setup allowed elements, SubtractiveWhitelist module
        $allowed_elements = $definition->config->get('HTML', 'AllowedElements');
        if (is_array($allowed_elements)) {
            foreach ($definition->info as $name => $d) {
                if(!isset($allowed_elements[$name])) unset($definition->info[$name]);
            }
        }
        $allowed_attributes = $definition->config->get('HTML', 'AllowedAttributes');
        if (is_array($allowed_attributes)) {
            foreach ($definition->info_global_attr as $attr_key => $info) {
                if (!isset($allowed_attributes["*.$attr_key"])) {
                    unset($definition->info_global_attr[$attr_key]);
                }
            }
            foreach ($definition->info as $tag => $info) {
                foreach ($info->attr as $attr => $attr_info) {
                    if (!isset($allowed_attributes["$tag.$attr"]) &&
                        !isset($allowed_attributes["*.$attr"])) {
                        unset($definition->info[$tag]->attr[$attr]);
                    }
                }
            }
        }
        
    }
    
}

?>