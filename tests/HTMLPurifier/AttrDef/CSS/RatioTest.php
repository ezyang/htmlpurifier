<?php

class HTMLPurifier_AttrDef_CSS_RatioTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Ratio();

        $this->assertDef('1/2');
        $this->assertDef('1 / 2', '1/2');
        $this->assertDef('1');
        $this->assertDef('1/0');
        $this->assertDef('0/1');

        $this->assertDef('1/2/3', false);
        $this->assertDef('/2/3', false);
        $this->assertDef('/12', false);
        $this->assertDef('1/', false);
        $this->assertDef('asdf', false);

        $this->assertDef('auto');
        $this->assertDef('AUTO', 'auto');
        $this->assertDef('auto 1/2');
        $this->assertDef('1/2 auto');
        $this->assertDef('auto 1 / 2', 'auto 1/2');
        $this->assertDef('1 / 2 auto', '1/2 auto');

        $this->assertDef('auto1/2', false);
        $this->assertDef('1/2auto', false);
        $this->assertDef('auto auto', false);
    }
}

// vim: et sw=4 sts=4
