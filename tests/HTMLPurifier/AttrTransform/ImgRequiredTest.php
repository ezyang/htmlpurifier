<?php

require_once 'HTMLPurifier/AttrTransform/ImgRequired.php';

class HTMLPurifier_AttrTransform_ImgRequiredTest extends HTMLPurifier_AttrTransformHarness
{
    
    function test() {
        
        $this->transform = new HTMLPurifier_AttrTransform_ImgRequired();
        
        $inputs = $expect = $config = array();
        
        $inputs[0] = array();
        $expect[0] = array('src' => '', 'alt' => 'Invalid image');
        
        $inputs[1] = array();
        $expect[1] = array('src' => 'blank.png', 'alt' => 'Pawned!');
        $config[1] = HTMLPurifier_Config::createDefault();
        $config[1]->set('Attr', 'DefaultInvalidImage', 'blank.png');
        $config[1]->set('Attr', 'DefaultInvalidImageAlt', 'Pawned!');
        
        $inputs[2] = array('src' => '/path/to/foobar.png');
        $expect[2] = array('src' => '/path/to/foobar.png', 'alt' => 'foobar.png');
        
        $inputs[3] = array('alt' => 'intrigue');
        $expect[3] = array('src' => '', 'alt' => 'intrigue');
        
        $this->assertTransform($inputs, $expect, $config);
        
    }
    
}

?>