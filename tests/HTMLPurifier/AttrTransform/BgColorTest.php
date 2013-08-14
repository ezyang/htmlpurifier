<?php

// we currently rely on the CSS validator to fix any problems.
// This means that this transform, strictly speaking, supports
// a superset of the functionality.

class HTMLPurifier_AttrTransform_BgColorTest extends HTMLPurifier_AttrTransformHarness
{

    public function setUp()
    {
        parent::setUp();
        $this->obj = new HTMLPurifier_AttrTransform_BgColor();
    }

    public function testEmptyInput()
    {
        $this->assertResult( array() );
    }

    public function testBasicTransform()
    {
        $this->assertResult(
            array('bgcolor' => '#000000'),
            array('style' => 'background-color:#000000;')
        );
    }

    public function testPrependNewCSS()
    {
        $this->assertResult(
            array('bgcolor' => '#000000', 'style' => 'font-weight:bold'),
            array('style' => 'background-color:#000000;font-weight:bold')
        );
    }

    public function testLenientTreatmentOfInvalidInput()
    {
        // this may change when we natively support the datatype and
        // validate its contents before forwarding it on
        $this->assertResult(
            array('bgcolor' => '#F00'),
            array('style' => 'background-color:#F00;')
        );
    }

}

// vim: et sw=4 sts=4
