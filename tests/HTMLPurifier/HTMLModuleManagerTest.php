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
        $this->manager = new HTMLPurifier_HTMLModuleManager(true);
    }
    
    function teardown() {
        tally_errors($this);
    }
    
    function createModule($name) {
        $module = new HTMLPurifier_HTMLModule();
        $module->name = $name;
        return $module;
    }
    
    function test_addModule_withAutoload() {
        $this->manager->autoDoctype = 'Generic Document 0.1';
        $this->manager->autoCollection = 'Default';
        
        $module = new HTMLPurifier_HTMLModule();
        $module->name = 'Module';
        
        $module2 = new HTMLPurifier_HTMLModule();
        $module2->name = 'Module2';
        
        // we need to grab the dynamically generated orders from
        // the object since modules are not passed by reference
        
        $this->manager->addModule($module);
        $module_order = $this->manager->modules['Module']->order;
        $module->order = $module_order;
        $this->assertEqual($module, $this->manager->modules['Module']);
        
        $this->manager->addModule($module2);
        $module2_order = $this->manager->modules['Module2']->order;
        $module2->order = $module2_order;
        $this->assertEqual($module2, $this->manager->modules['Module2']);
        $this->assertEqual($module_order + 1, $module2_order);
        
        $this->assertEqual(
            $this->manager->collections['Default']['Generic Document 0.1'],
            array('Module', 'Module2')
        );
        
        $this->manager->setup(HTMLPurifier_Config::createDefault());
        
        $modules = array(
            'Module' => $this->manager->modules['Module'],
            'Module2' => $this->manager->modules['Module2']
        );
        
        $this->assertIdentical(
            $this->manager->collections['Default']['Generic Document 0.1'],
            $modules
        );
        $this->assertIdentical($this->manager->activeModules, $modules);
        $this->assertIdentical($this->manager->activeCollections, array('Default'));
        
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
            $disable = false;
            foreach ($col as $mod_i => $mod) {
                unset($expect[$col_i][$mod_i]);
                if ($mod_i === '*') {
                    $disable = true;
                    continue;
                }
                $expect[$col_i][$mod] = $this->manager->modules[$mod];
            }
            if ($disable) $expect[$col_i]['*'] = false;
        }
        $this->assertIdentical($input, $expect);
    }
    
    function testImpl_processCollections() {
        $this->manager->initialize();
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
        // strange but valid stuff that will be handled in assembleModules
        $this->assertProcessCollections(
            array(
                'Linky' => array('Hypertext'),
                'Listy' => array('List'),
                '*' => array('Text')
            )
        );
        $this->assertProcessCollections(
            array(
                'Linky' => array('Hypertext'),
                'ListyOnly' => array('List', '*' => false),
                '*' => array('Text')
            )
        );
    }
    
    function testImpl_processCollections_error() {
        $this->manager->initialize();
        
        $this->expectError( // active variables, watch out!
            'Illegal inclusion array at index 1 found collection HTML, '.
            'inclusion arrays must be at start of collection (index 0)');
        $c = array(
            'HTML' => array('Legacy', array('XHTML')),
            'XHTML' => array('Text', 'Hypertext')
        );
        $this->manager->processCollections($c);
        unset($c);
        
        $this->expectError('Collection HTML references undefined '.
            'module Foobar');
        $c = array(
            'HTML' => array('Foobar')
        );
        $this->manager->processCollections($c);
        unset($c);
        
        $this->expectError('Collection HTML tried to include undefined '.
            'collection _Common');
        $c = array(
            'HTML' => array(array('_Common'), 'Legacy')
        );
        $this->manager->processCollections($c);
        unset($c);
        
        // reports the first circular inclusion it runs across
        $this->expectError('Circular inclusion detected in HTML collection');
        $c = array(
            'HTML' => array(array('XHTML')),
            'XHTML' => array(array('HTML'))
        );
        $this->manager->processCollections($c);
        unset($c);
        
    }
    
    function test_makeCollection() {
        $config = HTMLPurifier_Config::create(array(
            'HTML.Doctype' => 'Custom Doctype'
        ));
        $this->manager->addModule($this->createModule('ActiveModule'));
        $this->manager->addModule($this->createModule('DudModule'));
        $this->manager->addModule($this->createModule('ValidModule'));
        $ActiveModule   = $this->manager->modules['ActiveModule'];
        $DudModule      = $this->manager->modules['DudModule'];
        $ValidModule    = $this->manager->modules['ValidModule'];
        $this->manager->collections['ToBeValid']['Custom Doctype'] = array('ValidModule');
        $this->manager->collections['ToBeActive']['Custom Doctype'] = array('ActiveModule');
        $this->manager->makeCollectionValid('ToBeValid');
        $this->manager->makeCollectionActive('ToBeActive');
        $this->manager->setup($config);
        $this->assertIdentical($this->manager->validModules, array(
            'ValidModule'  => $ValidModule,
            'ActiveModule' => $ActiveModule
        ));
        $this->assertIdentical($this->manager->activeModules, array(
            'ActiveModule' => $ActiveModule
        ));
    }
    
    function test_makeCollection_undefinedCollection() {
        $config = HTMLPurifier_Config::create(array(
            'HTML.Doctype' => 'Sweets Document 1.0'
        ));
        $this->manager->addModule($this->createModule('DonutsModule'));
        $this->manager->addModule($this->createModule('ChocolateModule'));
        $this->manager->collections['CocoaBased']['Sweets Document 1.0'] = array('ChocolateModule');
        // notice how BreadBased collection is missing
        $this->manager->makeCollectionActive('CocoaBased'); // to prevent other errors
        $this->manager->makeCollectionValid('BreadBased');
        $this->expectError('BreadBased collection is undefined');
        $this->manager->setup($config);
    }
    
    function untest_soupStuff() {
        $config = HTMLPurifier_Config::create(array(
            'HTML.Doctype' => 'The Soup Specification 8.0'
        ));
        $this->manager->addModule($this->createModule('VegetablesModule'));
        $this->manager->addModule($this->createModule('MeatModule'));
        
    }
    
    
}

?>