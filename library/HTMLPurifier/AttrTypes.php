<?php

require_once 'HTMLPurifier/AttrDef/Nmtokens.php';
require_once 'HTMLPurifier/AttrDef/Text.php';
require_once 'HTMLPurifier/AttrDef/ID.php';
require_once 'HTMLPurifier/AttrDef/URI.php';

/**
 * Provides lookup array of attribute types to HTMLPurifier_AttrDef objects
 */
class HTMLPurifier_AttrTypes
{
    var $info = array();
    function HTMLPurifier_AttrTypes() {
        $this->info['NMTOKENS'] = new HTMLPurifier_AttrDef_Nmtokens();
        $this->info['CDATA'] = new HTMLPurifier_AttrDef_Text();
        $this->info['ID'] = new HTMLPurifier_AttrDef_ID();
        $this->info['URI'] = new HTMLPurifier_AttrDef_URI();
    }
}

?>