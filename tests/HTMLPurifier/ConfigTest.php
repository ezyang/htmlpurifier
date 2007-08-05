<?php

require_once 'HTMLPurifier/Config.php';

if (!class_exists('CS')) {
    class CS extends HTMLPurifier_ConfigSchema {}
}

class HTMLPurifier_ConfigTest extends HTMLPurifier_Harness
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
        $config->autoFinalize = false;
        
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
        
        $this->expectError('Cannot set undefined directive Element.Metal to value');
        $config->set('Element', 'Metal', true);
        
        $this->expectError('Value for Element.Radioactive is of invalid type, should be bool');
        $config->set('Element', 'Radioactive', 'very');
        
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
        
        $this->expectError('Cannot retrieve value of undefined directive Element.Metal');
        $config->get('Element', 'Metal');
        
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
        $config->autoFinalize = false;
        
        // case sensitive
        
        $config->set('Instrument', 'Manufacturer', 'Vandoren');
        $this->assertIdentical($config->get('Instrument', 'Manufacturer'), 'Vandoren');
        
        $config->set('Instrument', 'Manufacturer', 'Selmer');
        $this->assertIdentical($config->get('Instrument', 'Manufacturer'), 'Conn-Selmer');
        
        $this->expectError('Value not supported, valid values are: Yamaha, Conn-Selmer, Vandoren, Laubin, Buffet, other');
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
        $config->autoFinalize = false;
        
        $config->set('ReportCard', 'English', 'B-');
        $this->assertIdentical($config->get('ReportCard', 'English'), 'B-');
        
        $config->set('ReportCard', 'English', null); // not yet graded
        $this->assertIdentical($config->get('ReportCard', 'English'), null);
        
        // error
        $this->expectError('Value for ReportCard.Absences is of invalid type, should be int');
        $config->set('ReportCard', 'Absences', null);
        
    }
    
    function testAliases() {
        
        CS::defineNamespace('Home', 'Sweet home.');
        CS::define('Home', 'Rug', 3, 'int', 'ID.');
        CS::defineAlias('Home', 'Carpet', 'Home', 'Rug');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;
        
        $this->assertIdentical($config->get('Home', 'Rug'), 3);
        
        $this->expectError('Cannot get value from aliased directive, use real name Home.Rug');
        $config->get('Home', 'Carpet');
        
        $config->set('Home', 'Carpet', 999);
        $this->assertIdentical($config->get('Home', 'Rug'), 999);
        
    }
    
    // test functionality based on method
    
    function test_getBatch() {
        
        CS::defineNamespace('Variables', 'Changing quantities in equation.');
        CS::define('Variables', 'TangentialAcceleration', 'a_tan', 'string', 'In m/s^2');
        CS::define('Variables', 'AngularAcceleration', 'alpha', 'string', 'In rad/s^2');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;
        
        // grab a namespace
        $this->assertIdentical(
            $config->getBatch('Variables'),
            array(
                'TangentialAcceleration' => 'a_tan',
                'AngularAcceleration' => 'alpha'
            )
        );
        
        // grab a non-existant namespace
        $this->expectError('Cannot retrieve undefined namespace Constants');
        $config->getBatch('Constants');
        
    }
    
    function test_loadIni() {
        
        CS::defineNamespace('Shortcut', 'Keyboard shortcuts for commands');
        CS::define('Shortcut', 'Copy', 'c', 'istring', 'Copy text');
        CS::define('Shortcut', 'Paste', 'v', 'istring', 'Paste clipboard');
        CS::define('Shortcut', 'Cut', 'x', 'istring', 'Cut text');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;
        
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
        $config->set('HTML', 'Doctype', 'XHTML 1.0 Strict');
        $config->autoFinalize = false;
        
        $def = $config->getCSSDefinition();
        $this->assertIsA($def, 'HTMLPurifier_CSSDefinition');
        
        $def  =& $config->getHTMLDefinition();
        $def2 =& $config->getHTMLDefinition();
        $this->assertIsA($def, 'HTMLPurifier_HTMLDefinition');
        $this->assertReference($def, $def2);
        $this->assertTrue($def->setup);
        
        // test re-calculation if HTML changes
        unset($def, $def2);
        $def2 = $config->getHTMLDefinition(); // forcibly de-reference
        
        $config->set('HTML', 'Doctype', 'HTML 4.01 Transitional');
        $def = $config->getHTMLDefinition();
        $this->assertIsA($def, 'HTMLPurifier_HTMLDefinition');
        $this->assertNotEqual($def, $def2);
        $this->assertTrue($def->setup);
        
        // test retrieval of raw definition
        $config->set('HTML', 'DefinitionID', 'HTMLPurifier_ConfigTest->test_getHTMLDefinition()');
        $config->set('HTML', 'DefinitionRev', 3);
        $def =& $config->getHTMLDefinition(true);
        $this->assertNotEqual($def, $def2);
        $this->assertEqual(false, $def->setup);
        
        // auto initialization
        $config->getHTMLDefinition();
        $this->assertTrue($def->setup);
        
    }
    
    function test_getHTMLDefinition_rawError() {
        $this->old_copy = HTMLPurifier_ConfigSchema::instance($this->old_copy);
        $config = HTMLPurifier_Config::createDefault();
        $this->expectError('Cannot retrieve raw version without specifying %HTML.DefinitionID');
        $def =& $config->getHTMLDefinition(true);
    }
    
    function test_getCSSDefinition() {
        $this->old_copy = HTMLPurifier_ConfigSchema::instance($this->old_copy);
        $config = HTMLPurifier_Config::createDefault();
        $def = $config->getCSSDefinition();
        $this->assertIsA($def, 'HTMLPurifier_CSSDefinition');
    }
    
    function test_getDefinition() {
        CS::defineNamespace('Cache', 'Cache stuff');
        CS::define('Cache', 'DefinitionImpl', null, 'string/null', 'Cache?');
        CS::defineNamespace('Crust', 'Krusty Krabs');
        $config = HTMLPurifier_Config::createDefault();
        $this->expectError("Definition of Crust type not supported");
        $config->getDefinition('Crust');
    }
    
    function test_loadArray() {
        // setup a few dummy namespaces/directives for our testing
        CS::defineNamespace('Zoo', 'Animals we have.');
        CS::define('Zoo', 'Aadvark', 0, 'int', 'Have?');
        CS::define('Zoo', 'Boar',    0, 'int', 'Have?');
        CS::define('Zoo', 'Camel',   0, 'int', 'Have?');
        CS::define(
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
        
        $this->assertIdentical($config_manual, $config_loadabbr);
        $this->assertIdentical($config_manual, $config_loadfull);
        
    }
    
    function test_create() {
        
        CS::defineNamespace('Cake', 'Properties of it.');
        CS::define('Cake', 'Sprinkles', 666, 'int', 'Number of.');
        CS::define('Cake', 'Flavor', 'vanilla', 'string', 'Flavor of the batter.');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cake', 'Sprinkles', 42);
        
        // test flat pass-through
        $created_config = HTMLPurifier_Config::create($config);
        $this->assertIdentical($config, $created_config);
        
        // test loadArray
        $created_config = HTMLPurifier_Config::create(array('Cake.Sprinkles' => 42));
        $this->assertIdentical($config, $created_config);
        
        // test loadIni
        $created_config = HTMLPurifier_Config::create(dirname(__FILE__) . '/ConfigTest-create.ini');
        $this->assertIdentical($config, $created_config);
        
    }
    
    function test_finalize() {
        
        // test finalization
        
        CS::defineNamespace('Poem', 'Violets are red, roses are blue...');
        CS::define('Poem', 'Meter', 'iambic', 'string', 'Rhythm of poem.');
        
        $config = HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;
        
        $config->set('Poem', 'Meter', 'irregular');
        
        $config->finalize();
        
        $this->expectError('Cannot set directive after finalization');
        $config->set('Poem', 'Meter', 'vedic');
        
        $this->expectError('Cannot load directives after finalization');
        $config->loadArray(array('Poem.Meter' => 'octosyllable'));
        
        $this->expectError('Cannot load directives after finalization');
        $config->loadIni(dirname(__FILE__) . '/ConfigTest-finalize.ini');
        
    }
    
    function test_loadArrayFromForm() {
        
        CS::defineNamespace('Pancake', 'This should not be user customizable');
        CS::define('Pancake', 'Mix', 'buttermilk', 'string', 'Type of pancake mix to use.');
        CS::define('Pancake', 'Served', true, 'bool', 'But this is customizable by user.');
        CS::defineNamespace('Toppings', 'This is user customizable');
        CS::define('Toppings', 'Syrup', true, 'bool', 'Absolutely standard!');
        CS::define('Toppings', 'Flavor', 'maple', 'string', 'What flavor is the syrup?');
        CS::define('Toppings', 'Strawberries', 3, 'int', 'Quite delightful fruit.');
        CS::define('Toppings', 'Calories', 2000, 'int/null', 'Some things are best left unknown.');
        CS::define('Toppings', 'DefinitionID', null, 'string/null', 'Do not let this be set');
        CS::define('Toppings', 'DefinitionRev', 1, 'int', 'Do not let this be set');
        CS::define('Toppings', 'Protected', 1, 'int', 'Do not let this be set');
        
        $get = array(
            'breakfast' => array(
                'Pancake.Mix' => 'nasty',
                'Pancake.Served' => '0',
                'Toppings.Syrup' => '0',
                'Toppings.Flavor' => "juice",
                'Toppings.Strawberries' => '999',
                'Toppings.Calories' => '',
                'Null_Toppings.Calories' => '1',
                'Toppings.DefinitionID' => '<argh>',
                'Toppings.DefinitionRev' => '65',
                'Toppings.Protected' => '4',
            )
        );
        
        $config_expect = HTMLPurifier_Config::create(array(
            'Pancake.Served' => false,
            'Toppings.Syrup' => false,
            'Toppings.Flavor' => "juice",
            'Toppings.Strawberries' => 999,
            'Toppings.Calories' => null
        ));
        
        $config_result = HTMLPurifier_Config::loadArrayFromForm($get, 'breakfast', array('Pancake.Served', 'Toppings', '-Toppings.Protected'));
        
        $this->assertEqual($config_expect, $config_result);
        
        /*
        MAGIC QUOTES NOT TESTED!!!
        
        $get = array(
            'breakfast' => array(
                'Pancake.Mix' => 'n\\asty'
            )
        );
        $config_expect = HTMLPurifier_Config::create(array(
            'Pancake.Mix' => 'n\\asty'
        ));
        $config_result = HTMLPurifier_Config::loadArrayFromForm($get, 'breakfast', true, false);
        $this->assertEqual($config_expect, $config_result);
        */
    }
    
    function test_getAllowedDirectivesForForm() {
        CS::defineNamespace('Unused', 'Not mentioned, so deny');
        CS::define('Unused', 'Unused', 'Foobar', 'string', 'Not mentioned, do not allow');
        CS::defineNamespace('Partial', 'Some are mentioned, allow only those');
        CS::define('Partial', 'Allowed', true, 'bool', 'Mentioned, allowed');
        CS::define('Partial', 'Unused', 'Foobar', 'string', 'Not mentioned, do not allow');
        CS::defineNamespace('All', 'Entire namespace allowed, allow all unless...');
        CS::define('All', 'Allowed', true, 'bool', 'Not mentioned, allowed');
        CS::define('All', 'Blacklisted', 'Foobar', 'string', 'Specifically blacklisted');
        CS::define('All', 'DefinitionID', 'Foobar', 'string/null', 'Special case, auto-blacklisted');
        CS::define('All', 'DefinitionRev', 2, 'int', 'Special case, auto-blacklisted');
        
        $input = array('Partial.Allowed', 'All', '-All.Blacklisted');
        $output = HTMLPurifier_Config::getAllowedDirectivesForForm($input);
        $expect = array(
            array('Partial', 'Allowed'),
            array('All', 'Allowed')
        );
        
        $this->assertEqual($output, $expect);
        
    }
    
}

