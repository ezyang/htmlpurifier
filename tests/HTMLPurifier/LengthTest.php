<?php

require_once 'HTMLPurifier/Length.php';

class HTMLPurifier_LengthTest extends HTMLPurifier_Harness
{
    
    function testConstruct() {
        $l = new HTMLPurifier_Length('23', 'in');
        $this->assertIdentical($l->n, '23');
        $this->assertIdentical($l->unit, 'in');
    }
    
    function testMake() {
        $l = HTMLPurifier_Length::make('+23.4in');
        $this->assertIdentical($l->n, '+23.4');
        $this->assertIdentical($l->unit, 'in');
    }
    
    function testToString() {
        $l = new HTMLPurifier_Length('23', 'in');
        $this->assertIdentical($l->toString(), '23in');
    }
    
    function assertValidate($string, $expect = true, $disable_negative = false) {
        if ($expect === true) $expect = $string;
        $l = HTMLPurifier_Length::make($string);
        $result = $l->validate($disable_negative, $this->config, $this->context);
        if ($result === false) $this->assertIdentical($expect, false);
        else $this->assertIdentical($l->toString(), $expect);
    }
    
    function testValidate() {
        $this->assertValidate('0');
        $this->assertValidate('0px');
        $this->assertValidate('4.5px');
        $this->assertValidate('-4.5px');
        $this->assertValidate('3ex');
        $this->assertValidate('3em');
        $this->assertValidate('3in');
        $this->assertValidate('3cm');
        $this->assertValidate('3mm');
        $this->assertValidate('3pt');
        $this->assertValidate('3pc');
        $this->assertValidate('3PX', '3px');
        $this->assertValidate('3', false);
        $this->assertValidate('3miles', false);
        $this->assertValidate('-3mm', false, true); // no-negatives
    }
    
}
