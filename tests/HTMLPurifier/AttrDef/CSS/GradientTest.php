<?php

class HTMLPurifier_AttrDef_CSS_GradientTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Gradient();

        $this->assertDef('radial-gradient(rgb(255,0,0), #fff)', 'radial-gradient(rgb(255,0,0),#fff)'); // rm spaces
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

        // shape
        $this->assertDef('radial-gradient(circle,#fff,#000)');
        $this->assertDef('repeating-radial-gradient(ellipse,#fff,#000)');
        $this->assertDef('radial-gradient( circle ,#fff,#000)', 'radial-gradient(circle,#fff,#000)'); // trim

        // color size
        $this->assertDef('radial-gradient(#f00 5%,#0f0 15%,#00f 15px)');
        $this->assertDef('repeating-radial-gradient(#f00 5em,#0f0 15px,#00f 12cm)');

        // invalid function
        $this->assertDef('invalid-gradient(#f00 auto)', false);
        $this->assertDef('double-linear-gradient(#f00 auto)', false);

        // invalid parameter
        $this->assertDef('repeating-radial-gradient(#f00 auto)', false); // invalid size
        $this->assertDef('linear-gradient(#fff,#000,to right)', 'linear-gradient(#fff,#000)'); // wrong order
        $this->assertDef('radial-gradient(57grad,hsl(147,12%,54%))', 'radial-gradient(hsl(147,12%,54%))'); // no angle for radial-gradient
        $this->assertDef('linear-gradient(circle,hsla(255,0%,0%,0))', 'linear-gradient(hsla(255,0%,0%,0))'); // no shape for linear-gradient
        $this->assertDef('radial-gradient(square,hsl(147,12%,54%))', 'radial-gradient(hsl(147,12%,54%))'); // no square shape for radial-gradient
        $this->assertDef('repeating-linear-gradient(#fff,foo,#000)', 'repeating-linear-gradient(#fff,#000)');
        $this->assertDef('repeating-linear-gradient(#fff,,#000)', 'repeating-linear-gradient(#fff,#000)');
        $this->assertDef('linear-gradient(rgba(255,0,0,0)', false); // missing bracket
        $this->assertDef('repeating-radial-gradient(#f00 5)', false);
    }

}

