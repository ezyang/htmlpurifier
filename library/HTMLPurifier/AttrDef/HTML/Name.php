<?php

/**
 * Validates the HTML attribute name.
 */

class HTMLPurifier_AttrDef_HTML_Name extends HTMLPurifier_AttrDef
{

    public function validate($name, $config, $context) {

        // we purposely avoid using regex, hopefully this is faster

        $numbers_to_blank = array_fill_keys( array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), '' );

        $id = strtr( $name, $numbers_to_blank );  // remove numbers to create a pseudo-ID

        // below, we reuse code taken from the ID validation for our pseudo-ID

        $id = trim($id); // trim it

        if ($id === '') return false;

        // we purposely avoid using regex, hopefully this is faster

        if (ctype_alpha($id)) {
            $result = true;
        } else {
            if (!ctype_alpha(@$id[0])) return false;
            $trim = trim( // primitive style of regexps, I suppose
                $id,
                'A..Za..z0..9:-._'
              );
            $result = ($trim === '');
        }

        // if the pseudo-ID is valid, whitespaces stripped or not,
        // return the original name with no whitespaces, 
        // but if the pseudo-ID is not valid, let's return false.
        return $result ? str_replace( ' ', '', $name ) : false;

    }

}

// vim: et sw=4 sts=4
