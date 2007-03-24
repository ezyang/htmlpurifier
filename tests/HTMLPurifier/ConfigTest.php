<?php

require_once 'HTMLPurifier/Config.php';

if (!class_exists('CS')) {
    class CS extends HTMLPurifier_ConfigSchema {}
}

class HTMLPurifier_ConfigTest extends UnitTestCase
{
    
    var $our_copy, $old_copy;
    
    function setUp() {
        // set up a dummy schema object for testing
        $our_copy = new HTMLPurifier_ConfigSchema();
        $this->old_copy = HTMLPurifier_ConfigSchema::instance();
        $this->our_copy =& HTMLPurifier_ConfigSchema::instance($our_copy);
    }
    
    function tearDown() {
        HTMLPurifier_ConfigSchema::instance($this->old_copy);
        tally_errors($this);
    }
    
    // test functionality based on ConfigSchema
    
    function testNormal() {
        CS::defineNamespace('Element', 'Chemical substances that cannot be further decomposed');
        
        CS::define('Element', 'Abbr', 'H', 'string', 'Abbreviation of element name.');
        CS::define('Element', 'Name', 'hydrogen', 'istring', 'Full name of atoms.');
        CS::define('Element', 'Number', 1, 'int', 'Atomic number, is identity.');
        CS::define('Element', 'Mass', 1.00794, 'float', 'Atomic mass.');
        CS::define('Element', 'Radioactive', false, 'bool', 'Does it have rapid decay?');
        CS::define('Element', 'Isotopes', array(1 => true, 2 => true, 3 => true), 'lookup',
            'What numbers of neutrons for this element have been observed?');
        CS::define('Element', 'Traits', array('nonmetallic', 'odorless', 'flammable'), 'list',
            'What are general properties of the element?');
        CS::define('Element', 'IsotopeNames', array(1 => 'protium', 2 => 'deuterium', 3 => 'tritium'), 'hash',
            'Lookup hash of neutron counts to formal names.');
        CS::define('Element', 'Object', new stdClass(), 'mixed', 'Model representation.');
        
        $config = HTMLPurifier_Config::createDefault();
        
        // test default value retrieval
        $this->assertIdentical($config->get('Element', 'Abbr'), 'H');
        $this->assertIdentical($config->get('Element', 'Name'), 'hydrogen');
        $this->assertIdentical($config->get('Element', 'Number'), 1);
        $this->assertIdentical($config->get('Element', 'Mass'), 1.00794);
        $this->assertIdentical($config->get('Element', 'Radioactive'), false);
        $this->assertIdentical($config->get('Element', 'Isotopes'), array(1 => true, 2 => true, 3 => true));
        $this->assertIdentical($config->get('Element', 'Traits'), array('nonmetallic', 'odorless', 'flammable'));
        $this->assertIdentical($config->get('Element', 'IsotopeNames'), array(1 => 'protium', 2 => 'deuterium', 3 => 'tritium'));
        $this->assertIdentical($config->get('Element', 'Object'), new stdClass());
        
        // test setting values
        $config->set('Element', 'Abbr', 'Pu');
        $config->set('Element', 'Name', 'PLUTONIUM'); // test decaps
        $config->set('Element', 'Number', '94'); // test parsing
        $config->set('Element', 'Mass', '244.'); // test parsing
        $config->set('Element', 'Radioactive', true);
        $config->set('Element', 'Isotopes', array(238, 239)); // test inversion
        $config->set('Element', 'Traits', 'nuclear, heavy, actinide'); // test parsing
        $config->set('Element', 'IsotopeNames', array(238 => 'Plutonium-238', 239 => 'Plutonium-239'));
        $config->set('Element', 'Object', false); // unmodeled
        
        // test value retrieval
        $this->assertIdentical($config->get('Element', 'Abbr'), 'Pu');
        $this->assertIdentical($config->get('Element', 'Name'), 'plutonium');
        $this->assertIdentical($config->get('Element', 'Number'), 94);
        $this->assertIdentical($config->get('Element', 'Mass'), 244.);
        $this->assertIdentical($config->get('Element', 'Radioactive'), true);
        $this->assertIdentical($config->get('Element', 'Isotopes'), array(238 => true, 239 => true));
        $this->assertIdentical($config->get('Element', 'Traits'), array('nuclear', 'heavy', 'actinide'));
        $this->assertIdentical($config->get('Element', 'IsotopeNames'), array(238 => 'Plutonium-238', 239 => 'Plutonium-239'));
        $this->assertIdentical($config->get('Element', 'Object'), false);
        
        // errors
        
        $this->expectError('Cannot retrieve value of undefined directive');
        $config->get('Element', 'Metal');
        
        $this->expectError('Cannot set undefined directive to value');
        $config->set('Element', 'Metal', true);
        
        $this->expectError('Value is of invalid type');
        $config->set('Element', 'Radioactive', 'very');
        
    }
    
    function testEnumerated() {
        
        CS::defineNamespace('Instrument', 'Of the musical type.');
        
        // case sensitive
        CS::define('Instrument', 'Manufacturer', 'Yamaha', 'string', 'Who made it?');
        CS::defineAllowedValues('Instrument', 'Manufacturer', array(
            'Yamaha', 'Conn-Selmer', 'Vandoren', 'Laubin', 'Buffet', 'other'));
        CS::defineValueAliases('Instrument', 'Manufacturer', array(
            'Selmer' => 'Conn-Selmer'));
        
        // case insensitive
        CS::define('Instrument', 'Family', 'woodwind', 'istring', 'What family is it?');
        CS::defineAllowedValues('Instrument', 'Family', array(
            'brass', 'woodwind', 'percussion', 'string', 'keyboard', 'electronic'));
        CS::defineValueAliases('Instrument', 'Family', array(
            'synth' => 'electronic'));
        
        $config = HTMLPurifier_Config::createDefault();
        
        // case sensitive
        
        $config->set('Instrument', 'Manufacturer', 'Vandoren');
        $this->assertIdentical($config->get('Instrument', 'Manufacturer'), 'Vandoren');
        
        $config->set('Instrument', 'Manufacturer', 'Selmer');
        $this->assertIdentical($config->get('Instrument', 'Manufacturer'), 'Conn-Selmer');
        
        $this->expectError('Value not supported');
        $config->set('Instrument', 'Manufacturer', 'buffet');
        
        // case insensitive
        
        $config->set('Instrument', 'Family', 'brass');
        $this->assertIdentical($config->get('Instrument', 'Family'), 'brass');
        
        $config->set('Instrument', 'Family', 'PERCUSSION');
        $this->assertIdentical($config->get('Instrument', 'Family'), 'percussion');
        
        $config->set('Instrument', 'Family', 'synth');
        $this->assertIdentical($config->get('Instrument', 'Family'), 'electronic');
        
        $config->set('Instrument', 'Family', 'Synth');
        $this->assertIdentical($config->get('Instrument', 'Family'), 'electronic');
        
    }
    
    function testNull() {
        
        CS::defineNamespace('ReportCard', 'It is for grades.');
        CS::define('ReportCard', 'English', null, 'string/null', 'Grade from English class.');
        CS::define('ReportCard', 'Absences', 0, 'int', 'How many times missing from school?');
        
        $config = HTMLPurifier_Config::createDefault();
        
        $config->set('ReportCard', 'English', 'B-');
        $this->assertIdentical($config->get('ReportCard', 'English'), 'B-');
        
        $config->set('ReportCard', 'English', null); // not yet graded
        $this->assertIdentical($config->get('ReportCard', 'English'), null);
        
        // error
        $this->expectError('Value is of invalid type');
        $config->set('ReportCard', 'Absences', null);
        
    }
    
    function testAliases() {
        
        HTMLPurifier_ConfigSchema::defineNamespace('Home', 'Sweet home.');
        HTMLPurifier_ConfigSchema::define('Home', 'Rug', 3, 'int', 'ID.');
        HTMLPurifier_ConfigSchema::defineAlias('Home', 'Carpet', 'Home', 'Rug');
        
        $config = HTMLPurifier_Config::createDefault();
        
        $this->assertEqual($config->get('Home', 'Rug'), 3);
        
        $this->expectError('Cannot get value from aliased directive, use real name');
        $config->get('Home', 'Carpet');
        
        $config->set('Home', 'Carpet', 999);
        $this->assertEqual($config->get('Home', 'Rug'), 999);
        
    }
    
    // test functionality based on method
    
    function test_getBatch() {
        
        CS::defineNamespace('Variables', 'Changing quantities in equation.');
        CS::define('Variables', 'TangentialAcceleration', 'a_tan', 'string', 'In m/s^2');
        CS::define('Variables', 'AngularAcceleration', 'alpha', 'string', 'In rad/s^2');
        
        $config = HTMLPurifier_Config::createDefault();
        
        // grab a namespace
        $this->assertIdentical(
            $config->getBatch('Variables'),
            array(
                'TangentialAcceleration' => 'a_tan',
                'AngularAcceleration' => 'alpha'
            )
        );
        
        // grab a non-existant namespace
        $this->expectError('Cannot retrieve undefined namespace');
        $config->getBatch('Constants');
        
    }
    
    function test_loadIni() {
        
        CS::defineNamespace('Shortcut', 'Keyboard shortcuts for commands');
        CS::define('Shortcut', 'Copy', 'c', 'istring', 'Copy text');
        CS::define('Shortcut', 'Paste', 'v', 'istring', 'Paste clipboard');
        CS::define('Shortcut', 'Cut', 'x', 'istring', 'Cut text');
        
        $config = HTMLPurifier_Config::createDefault();
        
        $config->loadIni(dirname(__FILE__) . '/ConfigTest-loadIni.ini');
        
        $this->assertIdentical($config->get('Shortcut', 'Copy'), 'q');
        $this->assertIdentical($config->get('Shortcut', 'Paste'), 'p');
        $this->assertIdentical($config->get('Shortcut', 'Cut'), 't');
        
    }
    
    function test_getHTMLDefinition() {
        
        // we actually want to use the old copy, because the definition
        // generation routines have dependencies on configuration values
        
        $this->old_copy = HTMLPurifier_ConfigSchema::instance($this->old_copy);
        
        $config = HTMLPurifier_Config::createDefault();
        
        $def = $config->getCSSDefinition();
        $this->assertIsA($def, 'HTMLPurifier_CSSDefinition');
        
        $def = $config->getHTMLDefinition();
        $def2 = $config->getHTMLDefinition();
        $this->assertIsA($def, 'HTMLPurifier_HTMLDefinition');
        $this->assertEqual($def, $def2);
        $this->assertTrue($def->setup);
        
        // test re-calculation if HTML changes
        $config->set('HTML', 'Strict', true);
        $def = $config->getHTMLDefinition();
        $this->assertIsA($def, 'HTMLPurifier_HTMLDefinition');
        $this->assertNotEqual($def, $def2);
        $this->assertTrue($def->setup);
        
        // test retrieval of raw definition
        $def =& $config->getHTMLDefinition(true);
        $this->assertNotEqual($def, $def2);
        $this->assertFalse($def->setup);
        
        // auto initialization
        $config->getHTMLDefinition();
        $this->assertTrue($def->setup);
        
    }
    
    function test_getCSSDefinition() {
        $this->old_copy = HTMLPurifier_ConfigSchema::instance($this->old_copy);
        
        $config = HTMLPurifier_Config::createDefault();
        
        $def = $config->getCSSDefinition();
        $this->assertIsA($def, 'HTMLPurifier_CSSDefinition');
    }
    
    function test_loadArray() {
        // setup a few dummy namespaces/directives for our testing
        HTMLPurifier_ConfigSchema::defineNamespace('Zoo', 'Animals we have.');
        HTMLPurifier_ConfigSchema::define('Zoo', 'Aadvark', 0, 'int', 'Have?');
        HTMLPurifier_ConfigSchema::define('Zoo', 'Boar',    0, 'int', 'Have?');
        HTMLPurifier_ConfigSchema::define('Zoo', 'Camel',   0, 'int', 'Have?');
        HTMLPurifier_ConfigSchema::define(
            'Zoo', 'Others', array(), 'list', 'Other animals we have one of.'
        );
        
        $config_manual   = HTMLPurifier_Config::createDefault();
        $config_loadabbr = HTMLPurifier_Config::createDefault();
        $config_loadfull = HTMLPurifier_Config::createDefault();
        
        $config_manual->set('Zoo', 'Aadvark', 3);
        $config_manual->set('Zoo', 'Boar', 5);
        $config_manual->set('Zoo', 'Camel', 2000); // that's a lotta camels!
        $config_manual->set('Zoo', 'Others', array('Peacock', 'Dodo')); // wtf!
        
        // condensed form
        $config_loadabbr->loadArray(array(
            'Zoo.Aadvark' => 3,
            'Zoo.Boar' => 5,
            'Zoo.Camel' => 2000,
            'Zoo.Others' => array('Peacock', 'Dodo')
        ));
        
        // fully expanded form
        $config_loadfull->loadArray(array(
            'Zoo' => array(
                'Aadvark' => 3,
                'Boar' => 5,
                'Camel' => 2000,
                'Others' => array('Peacock', 'Dodo')
            )
        ));
        
        $this->assertEqual($config_manual, $config_loadabbr);
        $this->assertEqual($config_manual, $config_loadfull);
        
    }
    
    function test_create() {
        
        HTMLPurifier_ConfigSchema::defineNamespace('Cake', 'Properties of it.');
        HTMLPurifier_ConfigSchema::define('Cake', 'Sprinkles', 666, 'int', 'Number of.');
        HTMLPurifier_ConfigSchema::define('Cake', 'Flavor', 'vanilla', 'string', 'Flavor of the batter.');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cake', 'Sprinkles', 42);
        
        // test flat pass-through
        $created_config = HTMLPurifier_Config::create($config);
        $this->assertEqual($config, $created_config);
        
        // test loadArray
        $created_config = HTMLPurifier_Config::create(array('Cake.Sprinkles' => 42));
        $this->assertEqual($config, $created_config);
        
        // test loadIni
        $created_config = HTMLPurifier_Config::create(dirname(__FILE__) . '/ConfigTest-create.ini');
        $this->assertEqual($config, $created_config);
        
    }
    
}

?>