<?php

require_once 'HTMLPurifier/InjectorHarness.php';
require_once 'HTMLPurifier/Injector/PurifierLinkify.php';

class HTMLPurifier_Injector_PurifierLinkifyTest extends HTMLPurifier_InjectorHarness
{
    
    function setup() {
        parent::setup();
        $this->config = array(
            'AutoFormat.PurifierLinkify' => true,
            'AutoFormatParam.PurifierLinkifyDocURL' => '#%s'
        );
    }
    
    function testLinkify() {
        
        $this->assertResult('Foobar');
        $this->assertResult('20% off!');
        $this->assertResult('%Core namespace (not recognized)');
        $this->assertResult(
          '%Namespace.Directive',
          '<a href="#Namespace.Directive">%Namespace.Directive</a>'
        );
        $this->assertResult(
          'This %Namespace.Directive thing',
          'This <a href="#Namespace.Directive">%Namespace.Directive</a> thing'
        );
        $this->assertResult(
          '<div>This %Namespace.Directive thing</div>',
          '<div>This <a href="#Namespace.Directive">%Namespace.Directive</a> thing</div>'
        );
        $this->assertResult(
          '<a>%Namespace.Directive</a>'
        );
        
        
    }
    
    function testNeeded() {
        $this->expectError('Cannot enable PurifierLinkify injector because a is not allowed');
        $this->assertResult('%Namespace.Directive', true, array('AutoFormat.PurifierLinkify' => true, 'HTML.Allowed' => 'b'));
    }
    
}

