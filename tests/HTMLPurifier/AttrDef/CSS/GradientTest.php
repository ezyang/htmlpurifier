<?php

class HTMLPurifier_AttrDef_CSS_GradientTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Gradient();

        $this->assertDef('linear-gradient(rgb(255,0,0), #fff)', 'linear-gradient(rgb(255,0,0),#fff)'); // rm spaces
        $this->assertDef('linear-gradient(left,rgba(255,0,0,0),red)', 'linear-gradient(to left,rgba(255,0,0,0),#FF0000)');
        $this->assertDef('repeating-linear-gradient(#aaa,#bbb,#ccc,#ddd,#eee,#fff)'); // multiple colors

        // direction
        $this->assertDef('linear-gradient(to right,#fff,#000)');
        $this->assertDef('linear-gradient(to left,#eee,#aaa)');
        $this->assertDef('repeating-linear-gradient(to bottom right,#ccc,#ddd)', 'repeating-linear-gradient(to right bottom,#ccc,#ddd)'); // direction order
        $this->assertDef('repeating-linear-gradient(left top,#ccc,#ddd)', 'repeating-linear-gradient(to left top,#ccc,#ddd)');

        // angle
        $this->assertDef('linear-gradient(-145deg,#fff,#000)');
        $this->assertDef('linear-gradient(360rad,#fff,#000)');
        $this->assertDef('repeating-linear-gradient(12turn,#fff,#000,#123456)');

        // invalid parameter
        $this->assertDef('linear-gradient(#fff,#000,to right)', 'linear-gradient(#fff,#000)');
        $this->assertDef('repeating-linear-gradient(#fff,foo,#000)', 'repeating-linear-gradient(#fff,#000)');
        $this->assertDef('repeating-linear-gradient(#fff,,#000)', 'repeating-linear-gradient(#fff,#000)');
        $this->assertDef('linear-gradient(rgba(255,0,0,0)', false); // missing bracket
    }

}

