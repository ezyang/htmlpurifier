<?php

require_once 'HTMLPurifier/ConfigSchema.php';

if (!class_exists('CS')) {
    class CS extends HTMLPurifier_ConfigSchema {}
}

class HTMLPurifier_ConfigSchemaTest extends HTMLPurifier_Harness
{
    
    protected $schema;
    
    function setup() {
        $this->schema = new HTMLPurifier_ConfigSchema();
    }
    
    function tearDown() {
        tally_errors($this);
    }
    
    function test_defineNamespace() {
        $this->schema->addNamespace('http', $d = 'This is an internet protocol.');
        
        $this->assertIdentical($this->schema->info_namespace, array(
            'http' => new HTMLPurifier_ConfigDef_Namespace($d)
        ));
        
        $this->expectError('Cannot redefine namespace');
        $this->schema->addNamespace('http', 'It is used to serve webpages.');
        
        $this->expectError('Namespace name must be alphanumeric');
        $this->schema->addNamespace('ssh+http', 'This http is tunneled through SSH.');
        
        $this->expectError('Description must be non-empty');
        $this->schema->addNamespace('ftp', null);
    }
    
    function test_define() {
        $this->schema->addNamespace('Car', 'Automobiles, those gas-guzzlers!');
        
        $this->schema->add('Car', 'Seats', 5, 'int', $d = 'Standard issue.');
        
        $this->assertIdentical($this->schema->defaults['Car']['Seats'], 5);
        $this->assertIdentical($this->schema->info['Car']['Seats'],
            new HTMLPurifier_ConfigDef_Directive('int', $d)
        );
        
        $this->schema->add('Car', 'Age', null, 'int/null', $d = 'Not always known.');
        
        $this->assertIdentical($this->schema->defaults['Car']['Age'], null);
        $this->assertIdentical($this->schema->info['Car']['Age'], 
            new HTMLPurifier_ConfigDef_Directive('int', $d, true)
        );
        
        $this->expectError('Cannot define directive for undefined namespace');
        $this->schema->add('Train', 'Cars', 10, 'int', 'Including the caboose.');
        
        $this->expectError('Directive name must be alphanumeric');
        $this->schema->add('Car', 'Is it shiny?', true, 'bool', 'Indicates regular waxing.');
        
        $this->expectError('Invalid type for configuration directive');
        $this->schema->add('Car', 'Efficiency', 50, 'mpg', 'The higher the better.');
        
        $this->expectError('Default value does not match directive type');
        $this->schema->add('Car', 'Producer', 'Ford', 'int', 'ID of the company that made the car.');
        
        $this->expectError('Description must be non-empty');
        $this->schema->add('Car', 'ComplexAttribute', 'lawyers', 'istring', null);
    }
    
    function test_defineAllowedValues() {
        $this->schema->addNamespace('QuantumNumber', 'D');
        $this->schema->add('QuantumNumber', 'Spin', 0.5, 'float',
            'Spin of particle. Fourth quantum number, represented by s.');
        $this->schema->add('QuantumNumber', 'Current', 's', 'string',
            'Currently selected quantum number.');
        $this->schema->add('QuantumNumber', 'Difficulty', null, 'string/null', $d = 'How hard are the problems?');
        
        $this->schema->addAllowedValues( // okay, since default is null
            'QuantumNumber', 'Difficulty', array('easy', 'medium', 'hard')
        );
        
        $this->assertIdentical($this->schema->defaults['QuantumNumber']['Difficulty'], null);
        $this->assertIdentical($this->schema->info['QuantumNumber']['Difficulty'], 
            new HTMLPurifier_ConfigDef_Directive(
                'string',
                $d,
                true,
                array(
                    'easy' => true,
                    'medium' => true,
                    'hard' => true
                )
            )
        );
        
        $this->expectError('Cannot define allowed values for undefined directive');
        $this->schema->addAllowedValues(
            'SpaceTime', 'Symmetry', array('time', 'spatial', 'projective')
        );
        
        $this->expectError('Cannot define allowed values for directive whose type is not string');
        $this->schema->addAllowedValues(
            'QuantumNumber', 'Spin', array(0.5, -0.5)
        );
        
        $this->expectError('Default value must be in allowed range of variables');
        $this->schema->addAllowedValues(
            'QuantumNumber', 'Current', array('n', 'l', 'm') // forgot s!
        );
    }
    
    function test_defineValueAliases() {
        $this->schema->addNamespace('Abbrev', 'Stuff on abbreviations.');
        $this->schema->add('Abbrev', 'HTH', 'Happy to Help', 'string', $d = 'Three-letters');
        $this->schema->addAllowedValues(
            'Abbrev', 'HTH', array(
                'Happy to Help',
                'Hope that Helps',
                'HAIL THE HAND!'
            )
        );
        $this->schema->addValueAliases(
            'Abbrev', 'HTH', array(
                'happy' => 'Happy to Help',
                'hope' => 'Hope that Helps'
            )
        );
        $this->schema->addValueAliases( // delayed addition
            'Abbrev', 'HTH', array(
                'hail' => 'HAIL THE HAND!'
            )
        );
        
        $this->assertIdentical($this->schema->defaults['Abbrev']['HTH'], 'Happy to Help');
        $this->assertIdentical($this->schema->info['Abbrev']['HTH'], 
            new HTMLPurifier_ConfigDef_Directive(
                'string',
                $d,
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
        $this->schema->addValueAliases(
            'Abbrev', 'HTH', array(
                'head' => 'Head to Head'
            )
        );
        
        $this->expectError('Cannot define alias over allowed value');
        $this->schema->addValueAliases(
            'Abbrev', 'HTH', array(
                'Hope that Helps' => 'Happy to Help'
            )
        );
        
    }
   
    function testAlias() {
        $this->schema->addNamespace('Home', 'Sweet home.');
        $this->schema->add('Home', 'Rug', 3, 'int', 'ID.');
        $this->schema->addAlias('Home', 'Carpet', 'Home', 'Rug');
        
        $this->assertTrue(!isset($this->schema->defaults['Home']['Carpet']));
        $this->assertIdentical($this->schema->info['Home']['Carpet'], 
            new HTMLPurifier_ConfigDef_DirectiveAlias('Home', 'Rug')
        );
        
        $this->expectError('Cannot define directive alias in undefined namespace');
        $this->schema->addAlias('Store', 'Rug', 'Home', 'Rug');
        
        $this->expectError('Directive name must be alphanumeric');
        $this->schema->addAlias('Home', 'R.g', 'Home', 'Rug');
        
        $this->schema->add('Home', 'Rugger', 'Bob Max', 'string', 'Name of.');
        $this->expectError('Cannot define alias over directive');
        $this->schema->addAlias('Home', 'Rugger', 'Home', 'Rug');
        
        $this->expectError('Cannot define alias to undefined directive');
        $this->schema->addAlias('Home', 'Rug2', 'Home', 'Rugavan');
        
        $this->expectError('Cannot define alias to alias');
        $this->schema->addAlias('Home', 'Rug2', 'Home', 'Carpet');
    }
    
    function assertValid($var, $type, $ret = null) {
        $ret = ($ret === null) ? $var : $ret;
        $this->assertIdentical($this->schema->validate($var, $type), $ret);
    }
    
    function assertInvalid($var, $type) {
        $this->assertTrue(
            $this->schema->isError(
                $this->schema->validate($var, $type)
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
        $this->assertValid(array(), 'lookup');
        
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
            $this->schema->isError(
                $this->schema->validate(null, 'string', false)
            )
        );
        
        $this->assertFalse(
            $this->schema->isError(
                $this->schema->validate(null, 'string', true)
            )
        );
        
    }
    
}

