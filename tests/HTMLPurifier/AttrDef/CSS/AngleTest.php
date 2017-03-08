<?php

class HTMLPurifier_AttrDef_CSS_AngleTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Angle();

        $this->assertDef('300 deg', '300deg');
        $this->assertDef('140grad');
        $this->assertDef('3.4rad');
        $this->assertDef('0.25turn');

        // negative values
        $this->assertDef('-90deg');
        $this->assertDef('-120grad');
        $this->assertDef('-2.48rad');
        $this->assertDef('-0.4turn');

        // wrong values
        $this->assertDef('foo turn', false);
        $this->assertDef('54bar', false);

    }

}
