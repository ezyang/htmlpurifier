<?php

/**
 * Implements special behavior for class attribute (normally NMTOKENS)
 */
class HTMLPurifier_AttrDef_HTML_Class extends HTMLPurifier_AttrDef_HTML_Nmtokens
{
    protected function filter($tokens, $config, $context) {
        $allowed = $config->get('Attr.AllowedClasses');
        $forbidden = $config->get('Attr.ForbiddenClasses');
        $ret = array();
        foreach ($tokens as $token) {
            if (
                ($allowed === null || isset($allowed[$token])) &&
                !isset($forbidden[$token])
            ) {
                $ret[] = $token;
            }
        }
        return $ret;
    }
}
