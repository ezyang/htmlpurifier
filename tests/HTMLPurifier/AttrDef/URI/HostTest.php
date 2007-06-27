<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/URI/Host.php';

// takes a URI formatted host and validates it


class HTMLPurifier_AttrDef_URI_HostTest extends HTMLPurifier_AttrDefHarness
{
    
    function test() {
        
        $this->def = new HTMLPurifier_AttrDef_URI_Host();
        
        $this->assertDef('[2001:DB8:0:0:8:800:200C:417A]'); // IPv6
        $this->assertDef('124.15.6.89'); // IPv4
        $this->assertDef('www.google.com'); // reg-name
        
    }
    
}

