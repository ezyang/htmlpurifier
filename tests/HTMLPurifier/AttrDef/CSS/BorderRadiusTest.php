<?php

class HTMLPurifier_AttrDef_CSS_BorderRadiusTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_BorderRadius(
            new HTMLPurifier_AttrDef_CSS_Composite([
                new HTMLPurifier_AttrDef_CSS_Percentage(true),
                new HTMLPurifier_AttrDef_CSS_Length('0')
            ])
        );

        $this->assertDef('5px');
        $this->assertDef('1px 2px 3px 4px');
        $this->assertDef('1px 2px 3px 4px 5px', '1px 2px 3px 4px');

        // horizontal / vertical radii
        $this->assertDef('10px / 5px');
        $this->assertDef('10px/5px', '10px / 5px');
        $this->assertDef('1px 2px / 3px 4% 5em 6px');

        $this->assertDef('10px / 5px / 2px', false);
        $this->assertDef('10px /', false);
        $this->assertDef('/ 5px', false);
        $this->assertDef('10px / -5px', false);
    }

}

// vim: et sw=4 sts=4
