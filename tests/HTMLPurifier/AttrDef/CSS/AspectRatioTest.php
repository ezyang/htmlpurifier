<?php

class HTMLPurifier_AttrDef_CSS_AspectRatioTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_AspectRatio();

        // plain <ratio> passes through
        $this->assertDef('1/2');
        $this->assertDef('1 / 2', '1/2');
        $this->assertDef('1');

        // auto || <ratio>, in either order
        $this->assertDef('auto');
        $this->assertDef('AUTO', 'auto');
        $this->assertDef('auto 1/2');
        $this->assertDef('1/2 auto');
        $this->assertDef('auto 1 / 2', 'auto 1/2');
        $this->assertDef('1 / 2 auto', '1/2 auto');

        $this->assertDef('auto1/2', false);
        $this->assertDef('1/2auto', false);
        $this->assertDef('auto auto', false);

        // adversarial: extra tokens, comments and repeated separators
        // must be rejected, not partially consumed
        $this->assertDef('auto 1/2 garbage', false);
        $this->assertDef('auto/**/1/2', false);
        $this->assertDef('1//2 auto', false);
        $this->assertDef('auto auto 1/2', false);
        $this->assertDef('auto 1/2 auto', false);
    }
}

// vim: et sw=4 sts=4
