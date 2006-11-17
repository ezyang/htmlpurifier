<?php

require_once 'HTMLPurifier/AttrDefHarness.php';
require_once 'HTMLPurifier/AttrDef/ID.php';
require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_AttrDef_IDTest extends HTMLPurifier_AttrDefHarness
{
    
    function setUp() {
        parent::setUp();
        
        $id_accumulator = new HTMLPurifier_IDAccumulator();
        $this->context->register('IDAccumulator', $id_accumulator);
        $this->def = new HTMLPurifier_AttrDef_ID();
        
    }
    
    function test() {
        
        // valid ID names
        $this->assertDef('alpha');
        $this->assertDef('al_ha');
        $this->assertDef('a0-:.');
        $this->assertDef('a');
        
        // invalid ID names
        $this->assertDef('<asa', false);
        $this->assertDef('0123', false);
        $this->assertDef('.asa', false);
        
        // test duplicate detection
        $this->assertDef('once');
        $this->assertDef('once', false);
        
        // valid once whitespace stripped, but needs to be amended
        $this->assertDef(' whee ', 'whee');
        
    }
    
    function testPrefix() {
        
        $this->config->set('Attr', 'IDPrefix', 'user_');
        
        $this->assertDef('alpha', 'user_alpha');
        $this->assertDef('<asa', false);
        $this->assertDef('once', 'user_once');
        $this->assertDef('once', false);
        
        // if already prefixed, leave alone
        $this->assertDef('user_alas');
        $this->assertDef('user_user_alas'); // how to bypass
        
    }
    
    function testTwoPrefixes() {
        
        $this->config->set('Attr', 'IDPrefix', 'user_');
        $this->config->set('Attr', 'IDPrefixLocal', 'story95_');
        
        $this->assertDef('alpha', 'user_story95_alpha');
        $this->assertDef('<asa', false);
        $this->assertDef('once', 'user_story95_once');
        $this->assertDef('once', false);
        
        $this->assertDef('user_story95_alas');
        $this->assertDef('user_alas', 'user_story95_user_alas'); // !
        
        $this->config->set('Attr', 'IDPrefix', '');
        $this->assertDef('amherst'); // no affect when IDPrefix isn't set
        $this->assertError('%Attr.IDPrefixLocal cannot be used unless '.
            '%Attr.IDPrefix is set');
        // SimpleTest has a bug and throws a sprintf error
        // $this->assertNoErrors();
        $this->swallowErrors();
        
    }
    
}

?>