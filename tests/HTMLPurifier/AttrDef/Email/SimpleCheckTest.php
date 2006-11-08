<?php

require_once 'HTMLPurifier/AttrDef/Email/SimpleCheck.php';
require_once 'HTMLPurifier/AttrDef/EmailHarness.php';

class HTMLPurifier_AttrDef_Email_SimpleCheckTest
    extends HTMLPurifier_AttrDef_EmailHarness
{
    
    function setUp() {
        $this->def = new HTMLPurifier_AttrDef_Email_SimpleCheck();
    }
    
}

?>