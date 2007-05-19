<?php

require_once 'HTMLPurifier/HTMLModule/Tidy.php';

Mock::generatePartial(
    'HTMLPurifier_HTMLModule_Tidy',
    'HTMLPurifier_HTMLModule_Tidy_TestForConstruct',
    array('populate')
);

class HTMLPurifier_HTMLModule_TidyTest extends UnitTestCase
{
    
    function test_getFixesForLevel() {
        
        $module = new HTMLPurifier_HTMLModule_Tidy();
        $module->fixesForLevel['light'][]  = 'light-fix';
        $module->fixesForLevel['medium'][] = 'medium-fix';
        $module->fixesForLevel['heavy'][]  = 'heavy-fix';
        
        $this->assertIdentical(
            array(),
            $module->getFixesForLevel('none')
        );
        $this->assertIdentical(
            array('light-fix' => true),
            $module->getFixesForLevel('light')
        );
        $this->assertIdentical(
            array('light-fix' => true, 'medium-fix' => true),
            $module->getFixesForLevel('medium')
        );
        $this->assertIdentical(
            array('light-fix' => true, 'medium-fix' => true, 'heavy-fix' => true),
            $module->getFixesForLevel('heavy')
        );
        
        $this->expectError('Tidy level turbo not recognized');
        $module->getFixesForLevel('turbo');
        
    }
    
    function test_construct() {
        
        $i = 0; // counter, helps us isolate expectations
        
        // initialize partial mock
        $module = new HTMLPurifier_HTMLModule_Tidy_TestForConstruct($this);
        $module->fixesForLevel['light']  = array('light-fix-1', 'light-fix-2');
        $module->fixesForLevel['medium'] = array('medium-fix-1', 'medium-fix-2');
        $module->fixesForLevel['heavy']  = array('heavy-fix-1', 'heavy-fix-2');
        // $module->HTMLPurifier_HTMLModule_Tidy(); // constructor
        
        $config = HTMLPurifier_Config::create(array(
            'HTML.TidyLevel' => 'none'
        ));
        $module->expectAt($i++, 'populate', array(array()));
        $module->construct($config);
        
        // basic levels
        
        $config = HTMLPurifier_Config::create(array(
            'HTML.TidyLevel' => 'light'
        ));
        $module->expectAt($i++, 'populate', array($module->getFixesForLevel('light')));
        $module->construct($config);
        
        $config = HTMLPurifier_Config::create(array(
            'HTML.TidyLevel' => 'heavy'
        ));
        $module->expectAt($i++, 'populate', array($module->getFixesForLevel('heavy')));
        $module->construct($config);
        
        // fine grained tuning
        
        $config = HTMLPurifier_Config::create(array(
            'HTML.TidyLevel' => 'none',
            'HTML.TidyAdd'   => array('light-fix-1', 'medium-fix-1')
        ));
        $module->expectAt($i++, 'populate', array(array(
            'light-fix-1' => true,
            'medium-fix-1' => true
        )));
        $module->construct($config);
        
        $config = HTMLPurifier_Config::create(array(
            'HTML.TidyLevel' => 'medium',
            'HTML.TidyRemove'   => array('light-fix-1', 'medium-fix-1')
        ));
        $module->expectAt($i++, 'populate', array(array(
            'light-fix-2' => true,
            'medium-fix-2' => true
        )));
        $module->construct($config);
        
        // done
        
        $module->tally();
        
    }
    
}

?>