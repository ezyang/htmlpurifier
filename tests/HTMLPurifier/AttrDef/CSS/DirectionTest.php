<?php

class HTMLPurifier_AttrDef_CSS_DirectionTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Direction();

        $this->assertDef('to top');
        $this->assertDef('to left');
        $this->assertDef('to center');
        $this->assertDef('to right');
        $this->assertDef('to bottom');
        $this->assertDef('to left top');
        $this->assertDef('to center top');
        $this->assertDef('to right top');
        $this->assertDef('to left center');
        $this->assertDef('to right center');
        $this->assertDef('to left bottom');
        $this->assertDef('to center bottom');
        $this->assertDef('to right bottom');

        // reordered due to internal impl details
        $this->assertDef('to top left', 'to left top');
        $this->assertDef('to top center', 'to top');
        $this->assertDef('to top right', 'to right top');
        $this->assertDef('to center left', 'to left');
        $this->assertDef('to center center', 'to center');
        $this->assertDef('to center right', 'to right');
        $this->assertDef('to bottom left', 'to left bottom');
        $this->assertDef('to bottom center', 'to bottom');
        $this->assertDef('to bottom right', 'to right bottom');

        // trim spaces
        $this->assertDef('to    right     top', 'to right top');

        // invalid uses (we're going to be strict on these)
        $this->assertDef('right top', 'to right top');
        $this->assertDef('left', 'to left');
        $this->assertDef('bottom', 'to bottom');
        $this->assertDef('to foo bar', false);
        $this->assertDef('to top to left', false); // double to
        $this->assertDef('to left left', 'to left');
        $this->assertDef('to left right top bottom center left', 'to left bottom');

    }

}
