<?php

require_once 'HTMLPurifier/HTMLModuleManager.php';

// stub classes for unit testing
class HTMLPurifier_HTMLModule_ManagerTestModule extends HTMLPurifier_HTMLModule {
    var $name = 'ManagerTestModule';
}
class HTMLPurifier_HTMLModuleManagerTest_TestModule extends HTMLPurifier_HTMLModule {
    var $name = 'TestModule';
}

class HTMLPurifier_HTMLModuleManagerTest extends UnitTestCase
{
    
    /**
     * System under test, instance of HTMLPurifier_HTMLModuleManager.
     */
    var $manager;
    
    function setup() {
        $this->manager = new HTMLPurifier_HTMLModuleManager();
    }
    
    function teardown() {
        tally_errors($this);
    }
    
    function test_addModule() {
        $module = new HTMLPurifier_HTMLModule();
        $module->name = 'Module';
        
        $module2 = new HTMLPurifier_HTMLModule();
        $module2->name = 'Module2';
        
        $this->manager->addModule($module);
        $this->assertEqual($module, $this->manager->modules['Module']);
        $module_order = $this->manager->modules['Module']->order;
        
        $this->manager->addModule($module2);
        $this->assertEqual($module2, $this->manager->modules['Module2']);
        $module2_order = $this->manager->modules['Module2']->order;
        $this->assertEqual($module_order + 1, $module2_order);
    }
    
    function test_addModule_undefinedClass() {
        $this->expectError('TotallyCannotBeDefined module does not exist');
        $this->manager->addModule('TotallyCannotBeDefined');
    }
    
    function test_addModule_stringExpansion() {
        $this->manager->addModule('ManagerTestModule');
        $this->assertIsA($this->manager->modules['ManagerTestModule'],
            'HTMLPurifier_HTMLModule_ManagerTestModule');
    }
    
    function test_addPrefix() {
        $this->manager->addPrefix('HTMLPurifier_HTMLModuleManagerTest_');
        $this->manager->addModule('TestModule');
        $this->assertIsA($this->manager->modules['TestModule'],
            'HTMLPurifier_HTMLModuleManagerTest_TestModule');
    }
    
    function assertProcessCollections($input, $expect = false) {
        if ($expect === false) $expect = $input;
        $this->manager->processCollections($input);
        // substitute in modules for $expect
        foreach ($expect as $col_i => $col) {
            foreach ($col as $mod_i => $mod) {
                unset($expect[$col_i][$mod_i]);
                $expect[$col_i][$mod] = $this->manager->modules[$mod];
            }
        }
        $this->assertIdentical($input, $expect);
    }
    
    function testImpl_processCollections() {
        $this->assertProcessCollections(
            array()
        );
        $this->assertProcessCollections(
            array('HTML' => array('Text'))
        );
        $this->assertProcessCollections(
            array('HTML' => array('Text', 'Legacy'))
        );
        $this->assertProcessCollections( // order is important!
            array('HTML' => array('Legacy', 'Text')),
            array('HTML' => array('Text', 'Legacy'))
        );
        $this->assertProcessCollections( // privates removed after process
            array('_Private' => array('Legacy', 'Text')),
            array()
        );
        $this->assertProcessCollections( // inclusions come first
            array(
                'HTML' => array(array('XHTML'), 'Legacy'),
                'XHTML' => array('Text', 'Hypertext')
            ),
            array(
                'HTML' => array('Text', 'Hypertext', 'Legacy'),
                'XHTML' => array('Text', 'Hypertext')
            )
        );
        $this->assertProcessCollections(
            array(
                'HTML' => array(array('_Common'), 'Legacy'),
                '_Common' => array('Text', 'Hypertext')
            ),
            array(
                'HTML' => array('Text', 'Hypertext', 'Legacy')
            )
        );
        $this->assertProcessCollections( // nested inclusions
            array(
                'Full' => array(array('Minimal'), 'Hypertext'),
                'Minimal' => array(array('Bare'), 'List'),
                'Bare' => array('Text')
            ),
            array(
                'Full' => array('Text', 'Hypertext', 'List'),
                'Minimal' => array('Text', 'List'),
                'Bare' => array('Text')
            )
        );
    }
    
    function testImpl_processCollections_error() {
        $this->expectError( // active variables, watch out!
            'Illegal inclusion array at index 1 found collection HTML, '.
            'inclusion arrays must be at start of collection (index 0)');
        $this->manager->processCollections(
            $c = array(
                'HTML' => array('Legacy', array('XHTML')),
                'XHTML' => array('Text', 'Hypertext')
            )
        );
        
        $this->expectError('Collection HTML references undefined '.
            'module Foobar');
        $this->manager->processCollections(
            $c = array(
                'HTML' => array('Foobar')
            )
        );
        
        $this->expectError('Collection HTML tried to include undefined '.
            'collection _Common');
        $this->manager->processCollections(
            $c = array(
                'HTML' => array(array('_Common'), 'Legacy')
            )
        );
        
        // reports the first circular inclusion it runs across
        $this->expectError('Circular inclusion detected in HTML collection');
        $this->manager->processCollections(
            $c = array(
                'HTML' => array(array('XHTML')),
                'XHTML' => array(array('HTML'))
            )
        );
        
    }
    
}

?>