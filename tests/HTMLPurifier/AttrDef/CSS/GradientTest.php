<?php

class HTMLPurifier_AttrDef_CSS_GradientTest extends HTMLPurifier_AttrDefHarness
{

    public function test()
    {
        $this->def = new HTMLPurifier_AttrDef_CSS_Gradient();

        $this->assertDef('linear-gradient(rgb(255,0,0), #fff)', 'linear-gradient(rgb(255,0,0),#fff)'); // rm spaces
        $this->assertDef('linear-gradient(rgba(255,0,0,0),red)', 'linear-gradient(rgba(255,0,0,0),#FF0000)');

        $this->assertDef('linear-gradient(rgba(255,0,0,0)', false); // missing bracket
    }

}

