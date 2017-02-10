<?php

class HTMLPurifier_AttrDef_CSS_PositionTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Position();

        // explicitly cited in spec
        $this->assertDef('top');
        $this->assertDef('left');
        $this->assertDef('center');
        $this->assertDef('right');
        $this->assertDef('bottom');
        $this->assertDef('left top');
        $this->assertDef('center top');
        $this->assertDef('right top');
        $this->assertDef('left center');
        $this->assertDef('right center');
        $this->assertDef('left bottom');
        $this->assertDef('center bottom');
        $this->assertDef('right bottom');

        // reordered due to internal impl details
        $this->assertDef('top left', 'left top');
        $this->assertDef('top center', 'top');
        $this->assertDef('top right', 'right top');
        $this->assertDef('center left', 'left');
        $this->assertDef('center center', 'center');
        $this->assertDef('center right', 'right');
        $this->assertDef('bottom left', 'left bottom');
        $this->assertDef('bottom center', 'bottom');
        $this->assertDef('bottom right', 'right bottom');

        // invalid uses (we're going to be strict on these)
        $this->assertDef('foo bar', false);
        $this->assertDef('left left', 'left');
        $this->assertDef('left right top bottom center left', 'left bottom');

    }

}
