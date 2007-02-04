<?php

require_once 'HTMLPurifier/AttrDef/Nmtokens.php';
require_once 'HTMLPurifier/AttrDef/Text.php';
require_once 'HTMLPurifier/AttrDef/ID.php';
require_once 'HTMLPurifier/AttrDef/URI.php';
require_once 'HTMLPurifier/AttrDef/Pixels.php';
require_once 'HTMLPurifier/AttrDef/Length.php';
require_once 'HTMLPurifier/AttrDef/MultiLength.php';
require_once 'HTMLPurifier/AttrDef/Integer.php';

/**
 * Provides lookup array of attribute types to HTMLPurifier_AttrDef objects
 */
class HTMLPurifier_AttrTypes
{
    /**
     * Lookup array of attribute string identifiers to concrete implementations
     * @public
     */
    var $info = array();
    
    /**
     * Constructs the info array
     */
    function HTMLPurifier_AttrTypes() {
        $this->info['NMTOKENS'] = new HTMLPurifier_AttrDef_Nmtokens();
        $this->info['CDATA'] = new HTMLPurifier_AttrDef_Text();
        $this->info['Text'] = new HTMLPurifier_AttrDef_Text();
        $this->info['ID'] = new HTMLPurifier_AttrDef_ID();
        $this->info['URI'] = new HTMLPurifier_AttrDef_URI();
        $this->info['Pixels'] = new HTMLPurifier_AttrDef_Pixels();
        $this->info['Length'] = new HTMLPurifier_AttrDef_Length();
        $this->info['MultiLength'] = new HTMLPurifier_AttrDef_MultiLength();
        // number is really a positive integer, according to XML one or
        // more digits
        $this->info['Number'] = new HTMLPurifier_AttrDef_Integer(false, false, true);
    }
}

?>