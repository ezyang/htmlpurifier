<?php

require_once 'HTMLPurifier/StrategyHarness.php';
require_once 'HTMLPurifier/Strategy/Core.php';

class HTMLPurifier_HTMLModuleHarness extends HTMLPurifier_StrategyHarness
{
    function setup() {
        parent::setup();
        $this->obj = new HTMLPurifier_Strategy_Core();
    }
    
    function setupScaffold($module, $config = array()) {
        
        $this->config = HTMLPurifier_Config::create($config);
        $this->config->set('HTML', 'AllowedModules', $module);
        $def =& $this->config->getHTMLDefinition(true);
        $def->manager->addModule(new HTMLPurifier_HTMLModuleHarness_Scaffold());
        
    }
}

/**
 * Special module that defines scaffolding for easy unit testing
 */
class HTMLPurifier_HTMLModuleHarness_Scaffold extends HTMLPurifier_HTMLModule
{
    var $name = 'Scaffold';
    var $attr_collections = array(
        'Common' => array('ac:common' => 'Text'),
        'Core'   => array('ac:core'   => 'Text')
    );
}

?>