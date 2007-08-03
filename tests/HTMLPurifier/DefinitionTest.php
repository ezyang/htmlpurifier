<?php

require_once 'HTMLPurifier/Definition.php';

Mock::generatePartial(
        'HTMLPurifier_Definition',
        'HTMLPurifier_Definition_Testable',
        array('doSetup'));

class HTMLPurifier_DefinitionTest extends HTMLPurifier_Harness
{
    function test_setup() {
        $def = new HTMLPurifier_Definition_Testable();
        $config = HTMLPurifier_Config::createDefault();
        $def->expectOnce('doSetup', array($config));
        $def->setup($config);
    }
    function test_setup_redundant() {
        $def = new HTMLPurifier_Definition_Testable();
        $config = HTMLPurifier_Config::createDefault();
        $def->expectNever('doSetup');
        $def->setup = true;
        $def->setup($config);
    }
    function test_doSetup_abstract() {
        $def = new HTMLPurifier_Definition();
        $this->expectError('Cannot call abstract method');
        $config = HTMLPurifier_Config::createDefault();
        $def->doSetup($config);
    }
}

