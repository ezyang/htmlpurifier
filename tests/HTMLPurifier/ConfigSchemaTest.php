<?php

require_once 'HTMLPurifier/ConfigSchema.php';

if (!class_exists('CS')) {
    class CS extends HTMLPurifier_ConfigSchema {}
}

class HTMLPurifier_ConfigSchemaTest extends HTMLPurifier_Harness
{
    
    /**
     * Munged name of current file.
     */
    var $file;
    
    /**
     * Copy of the real ConfigSchema to revert to.
     */
    var $old_copy;
    
    /**
     * Copy of dummy ConfigSchema for testing purposes.
     */
    var $our_copy;
    
    function setUp() {
        // yes, I know this is slightly convoluted, but that's the price
        // you pay for using Singletons. Good thing we can overload it.
        
        // first, let's get a clean copy to do tests
        $our_copy = new HTMLPurifier_ConfigSchema();
        // get the old copy
        $this->old_copy = HTMLPurifier_ConfigSchema::instance();
        // put in our copy, and reassign to the REAL reference
        $this->our_copy =& HTMLPurifier_ConfigSchema::instance($our_copy);
        
        $this->file = $this->our_copy->mungeFilename(__FILE__);
    }
    
    function tearDown() {
        // testing is done, restore the old copy
        HTMLPurifier_ConfigSchema::instance($this->old_copy);
        tally_errors($this);
    }
    
    function test_defineNamespace() {
        CS::defineNamespace('http', $d = 'This is an internet protocol.');
        
        $this->assertIdentical($this->our_copy->info_namespace, array(
            'http' => new HTMLPurifier_ConfigDef_Namespace($d)
        ));
        
        $this->expectError('Cannot redefine namespace');
        CS::defineNamespace('http', 'It is used to serve webpages.');
        
        $this->expectError('Namespace name must be alphanumeric');
        CS::defineNamespace('ssh+http', 'This http is tunneled through SSH.');
        
        $this->expectError('Description must be non-empty');
        CS::defineNamespace('ftp', null);
    }
    
    function test_define() {
        CS::defineNamespace('Car', 'Automobiles, those gas-guzzlers!');
        
        CS::define('Car', 'Seats', 5, 'int', $d = 'Standard issue.'); $l = __LINE__;
        
        $this->assertIdentical($this->our_copy->defaults['Car']['Seats'], 5);
        $this->assertIdentical($this->our_copy->info['Car']['Seats'],
            new HTMLPurifier_ConfigDef_Directive('int',
                array($this->file => array($l => $d))
            )
        );
        
        CS::define('Car', 'Age', null, 'int/null', $d = 'Not always known.'); $l = __LINE__;
        
        $this->assertIdentical($this->our_copy->defaults['Car']['Age'], null);
        $this->assertIdentical($this->our_copy->info['Car']['Age'], 
            new HTMLPurifier_ConfigDef_Directive('int',
                array($this->file => array($l => $d)), true
            )
        );
        
        $this->expectError('Cannot define directive for undefined namespace');
        CS::define('Train', 'Cars', 10, 'int', 'Including the caboose.');
        
        $this->expectError('Directive name must be alphanumeric');
        CS::define('Car', 'Is it shiny?', true, 'bool', 'Indicates regular waxing.');
        
        $this->expectError('Invalid type for configuration directive');
        CS::define('Car', 'Efficiency', 50, 'mpg', 'The higher the better.');
        
        $this->expectError('Default value does not match directive type');
        CS::define('Car', 'Producer', 'Ford', 'int', 'ID of the company that made the car.');
        
        $this->expectError('Description must be non-empty');
        CS::define('Car', 'ComplexAttribute', 'lawyers', 'istring', null);
    }
    
    function testRedefinition_define() {
        CS::defineNamespace('Cat', 'Belongs to Schrodinger.');
        
        CS::define('Cat', 'Dead', false, 'bool', $d1 = 'Well, is it?'); $l1 = __LINE__;
        CS::define('Cat', 'Dead', false, 'bool', $d2 = 'It is difficult to say.'); $l2 = __LINE__;
        
        $this->assertIdentical($this->our_copy->defaults['Cat']['Dead'], false);
        $this->assertIdentical($this->our_copy->info['Cat']['Dead'], 
            new HTMLPurifier_ConfigDef_Directive('bool',
                array($this->file => array($l1 => $d1, $l2 => $d2))
            )
        );
        
        $this->expectError('Inconsistent default or type, cannot redefine');
        CS::define('Cat', 'Dead', true, 'bool', 'Quantum mechanics does not know.');
        
        $this->expectError('Inconsistent default or type, cannot redefine');
        CS::define('Cat', 'Dead', 'maybe', 'string', 'Perhaps if we look we will know.');
    }
    
    function test_defineAllowedValues() {
        CS::defineNamespace('QuantumNumber', 'D');
        CS::define('QuantumNumber', 'Spin', 0.5, 'float',
            'Spin of particle. Fourth quantum number, represented by s.');
        CS::define('QuantumNumber', 'Current', 's', 'string',
            'Currently selected quantum number.');
        CS::define('QuantumNumber', 'Difficulty', null, 'string/null', $d = 'How hard are the problems?'); $l = __LINE__;
        
        CS::defineAllowedValues( // okay, since default is null
            'QuantumNumber', 'Difficulty', array('easy', 'medium', 'hard')
        );
        
        $this->assertIdentical($this->our_copy->defaults['QuantumNumber']['Difficulty'], null);
        $this->assertIdentical($this->our_copy->info['QuantumNumber']['Difficulty'], 
            new HTMLPurifier_ConfigDef_Directive(
                'string',
                array($this->file => array($l => $d)),
                true,
                array(
                    'easy' => true,
                    'medium' => true,
                    'hard' => true
                )
            )
        );
        
        $this->expectError('Cannot define allowed values for undefined directive');
        CS::defineAllowedValues(
            'SpaceTime', 'Symmetry', array('time', 'spatial', 'projective')
        );
        
        $this->expectError('Cannot define allowed values for directive whose type is not string');
        CS::defineAllowedValues(
            'QuantumNumber', 'Spin', array(0.5, -0.5)
        );
        
        $this->expectError('Default value must be in allowed range of variables');
        CS::defineAllowedValues(
            'QuantumNumber', 'Current', array('n', 'l', 'm') // forgot s!
        );
    }
    
    function test_defineValueAliases() {
        CS::defineNamespace('Abbrev', 'Stuff on abbreviations.');
        CS::define('Abbrev', 'HTH', 'Happy to Help', 'string', $d = 'Three-letters'); $l = __LINE__;
        CS::defineAllowedValues(
            'Abbrev', 'HTH', array(
                'Happy to Help',
                'Hope that Helps',
                'HAIL THE HAND!'
            )
        );
        CS::defineValueAliases(
            'Abbrev', 'HTH', array(
                'happy' => 'Happy to Help',
                'hope' => 'Hope that Helps'
            )
        );
        CS::defineValueAliases( // delayed addition
            'Abbrev', 'HTH', array(
                'hail' => 'HAIL THE HAND!'
            )
        );
        
        $this->assertIdentical($this->our_copy->defaults['Abbrev']['HTH'], 'Happy to Help');
        $this->assertIdentical($this->our_copy->info['Abbrev']['HTH'], 
            new HTMLPurifier_ConfigDef_Directive(
                'string',
                array($this->file => array($l => $d)),
                false,
                array(
                    'Happy to Help' => true,
                    'Hope that Helps' => true,
                    'HAIL THE HAND!' => true
                ),
                array(
                    'happy' => 'Happy to Help',
                    'hope' => 'Hope that Helps',
                    'hail' => 'HAIL THE HAND!'
                )
            )
        );
        
        $this->expectError('Cannot define alias to value that is not allowed');
        CS::defineValueAliases(
            'Abbrev', 'HTH', array(
                'head' => 'Head to Head'
            )
        );
        
        $this->expectError('Cannot define alias over allowed value');
        CS::defineValueAliases(
            'Abbrev', 'HTH', array(
                'Hope that Helps' => 'Happy to Help'
            )
        );
        
    }
   
    function testAlias() {
        CS::defineNamespace('Home', 'Sweet home.');
        CS::define('Home', 'Rug', 3, 'int', 'ID.');
        CS::defineAlias('Home', 'Carpet', 'Home', 'Rug');
        
        $this->assertTrue(!isset($this->our_copy->defaults['Home']['Carpet']));
        $this->assertIdentical($this->our_copy->info['Home']['Carpet'], 
            new HTMLPurifier_ConfigDef_DirectiveAlias('Home', 'Rug')
        );
        
        $this->expectError('Cannot define directive alias in undefined namespace');
        CS::defineAlias('Store', 'Rug', 'Home', 'Rug');
        
        $this->expectError('Directive name must be alphanumeric');
        CS::defineAlias('Home', 'R.g', 'Home', 'Rug');
        
        CS::define('Home', 'Rugger', 'Bob Max', 'string', 'Name of.');
        $this->expectError('Cannot define alias over directive');
        CS::defineAlias('Home', 'Rugger', 'Home', 'Rug');
        
        $this->expectError('Cannot define alias to undefined directive');
        CS::defineAlias('Home', 'Rug2', 'Home', 'Rugavan');
        
        $this->expectError('Cannot define alias to alias');
        CS::defineAlias('Home', 'Rug2', 'Home', 'Carpet');
    }
    
    function assertValid($var, $type, $ret = null) {
        $ret = ($ret === null) ? $var : $ret;
        $this->assertIdentical($this->our_copy->validate($var, $type), $ret);
    }
    
    function assertInvalid($var, $type) {
        $this->assertTrue(
            $this->our_copy->isError(
                $this->our_copy->validate($var, $type)
            )
        );
    }
    
    function testValidate() {
        
        $this->assertValid('foobar', 'string');
        $this->assertValid('foobar', 'text'); // aliases, lstring = long string
        $this->assertValid('FOOBAR', 'istring', 'foobar');
        $this->assertValid('FOOBAR', 'itext', 'foobar');
        
        $this->assertValid(34, 'int');
        
        $this->assertValid(3.34, 'float');
        
        $this->assertValid(false, 'bool');
        $this->assertValid(0, 'bool', false);
        $this->assertValid(1, 'bool', true);
        $this->assertValid('true', 'bool', true);
        $this->assertValid('false', 'bool', false);
        $this->assertValid('1', 'bool', true);
        $this->assertInvalid(34, 'bool');
        $this->assertInvalid(null, 'bool');
        
        $this->assertValid(array('1', '2', '3'), 'list');
        $this->assertValid('foo,bar, cow', 'list', array('foo', 'bar', 'cow'));
        $this->assertValid('', 'list', array());
        $this->assertValid("foo\nbar", 'list', array('foo', 'bar'));
        $this->assertValid("foo\nbar,baz", 'list', array('foo', 'bar', 'baz'));
        
        $this->assertValid(array('1' => true, '2' => true), 'lookup');
        $this->assertValid(array('1', '2'), 'lookup', array('1' => true, '2' => true));
        $this->assertValid('foo,bar', 'lookup', array('foo' => true, 'bar' => true));
        $this->assertValid("foo\nbar", 'lookup', array('foo' => true, 'bar' => true));
        $this->assertValid("foo\nbar,baz", 'lookup', array('foo' => true, 'bar' => true, 'baz' => true));
        $this->assertValid('', 'lookup', array());
        
        $this->assertValid(array('foo' => 'bar'), 'hash');
        $this->assertValid(array(1 => 'moo'), 'hash');
        $this->assertInvalid(array(0 => 'moo'), 'hash');
        $this->assertValid('', 'hash', array());
        $this->assertValid('foo:bar,too:two', 'hash', array('foo' => 'bar', 'too' => 'two'));
        $this->assertValid("foo:bar\ntoo:two,three:free", 'hash', array('foo' => 'bar', 'too' => 'two', 'three' => 'free'));
        $this->assertValid('foo:bar,too', 'hash', array('foo' => 'bar'));
        $this->assertValid('foo:bar,', 'hash', array('foo' => 'bar'));
        $this->assertValid('foo:bar:baz', 'hash', array('foo' => 'bar:baz'));
        
        $this->assertValid(23, 'mixed');
        
    }
    
    function testValidate_null() {
        
        $this->assertTrue(
            $this->our_copy->isError(
                $this->our_copy->validate(null, 'string', false)
            )
        );
        
        $this->assertFalse(
            $this->our_copy->isError(
                $this->our_copy->validate(null, 'string', true)
            )
        );
        
    }
    
    function assertMungeFilename($oldname, $newname) {
        $this->assertIdentical(
            $this->our_copy->mungeFilename($oldname),
            $newname
        );
    }
    
    function testMungeFilename() {
        
        $this->assertMungeFilename(
            'C:\\php\\My Libraries\\htmlpurifier\\library\\HTMLPurifier\\AttrDef.php',
            'HTMLPurifier/AttrDef.php'
        );
        
        $this->assertMungeFilename(
            'C:\\php\\My Libraries\\htmlpurifier\\library\\HTMLPurifier.php',
            'HTMLPurifier.php'
        );
        
    }
    
}

