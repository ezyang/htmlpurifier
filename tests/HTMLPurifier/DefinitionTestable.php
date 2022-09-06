<?php

abstract class HTMLPurifier_TestDefinition extends HTMLPurifier_Definition
{
    public $info;
    public $info_candles;
    public $info_random;
}

Mock::generatePartial(
        'HTMLPurifier_TestDefinition',
        'HTMLPurifier_DefinitionTestable',
        array('doSetup'));

// vim: et sw=4 sts=4
