<?php

/**
 * Validates the MathML attribute ID.
 * @note This just checks that the ID is valid. It explicitly avoids checking
 *       or adding to the ID Accumulator because the MathML 3 DTD makes it a
 *       point to allow repeated IDs.
 */

class HTMLPurifier_AttrDef_MathML_ID extends HTMLPurifier_AttrDef
{

    /**
     * @param string $id
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($id, $config, $context)
    {

        $id = trim($id); // trim it first

        if ($id === '') {
            return false;
        }

        $prefix = $config->get('Attr.IDPrefix');
        if ($prefix !== '') {
            $prefix .= $config->get('Attr.IDPrefixLocal');
            // prevent re-appending the prefix
            if (strpos($id, $prefix) !== 0) {
                $id = $prefix . $id;
            }
        } elseif ($config->get('Attr.IDPrefixLocal') !== '') {
            trigger_error(
                '%Attr.IDPrefixLocal cannot be used unless ' .
                '%Attr.IDPrefix is set',
                E_USER_WARNING
            );
        }

        // we purposely avoid using regex, hopefully this is faster

        if ($config->get('Attr.ID.HTML5') === true) {
            if (preg_match('/[\t\n\x0b\x0c ]/', $id)) {
                return false;
            }
        } else {
            if (ctype_alpha($id)) {
                // OK
            } else {
                if (!ctype_alpha(@$id[0])) {
                    return false;
                }
                // primitive style of regexps, I suppose
                $trim = trim(
                    $id,
                    'A..Za..z0..9:-._'
                );
                if ($trim !== '') {
                    return false;
                }
            }
        }

        $regexp = $config->get('Attr.IDBlacklistRegexp');
        if ($regexp && preg_match($regexp, $id)) {
            return false;
        }

        // if no change was made to the ID, return the result
        // else, return the new id if stripping whitespace made it
        //     valid, or return false.
        return $id;
    }
}