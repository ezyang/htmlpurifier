<?php

require_once 'HTMLPurifier/AttrTransform/BdoDir.php';

class HTMLPurifier_AttrTransform_BdoDirTest extends HTMLPurifier_AttrTransformHarness
{
    
    function test() {
        
        $this->transform = new HTMLPurifier_AttrTransform_BdoDir();
        
        $inputs = array();
        $expect = array();
        $config = array();
        
        // add dir
        $inputs[0] = array();
        $expect[0] = array('dir' => 'ltr');
        
        // leave existing dir alone
        $inputs[1] = array('dir' => 'rtl');
        $expect[1] = array('dir' => 'rtl');
        
        $config_rtl = HTMLPurifier_Config::createDefault();
        $config_rtl->set('Attr', 'DefaultTextDir', 'rtl');
        $inputs[2] = array();
        $expect[2] = array('dir' => 'rtl');
        $config[2] = $config_rtl;
        
        $this->assertTransform($inputs, $expect, $config);
        
    }
    
}

?>