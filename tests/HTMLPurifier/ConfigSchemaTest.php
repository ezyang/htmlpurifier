<?php

class HTMLPurifier_ConfigSchemaTest extends HTMLPurifier_Harness
{
    
    protected $schema;
    
    function setup() {
        $this->schema = new HTMLPurifier_ConfigSchema();
    }
    
    function test_defineNamespace() {
        $this->schema->addNamespace('http');
        $this->assertIdentical($this->schema->info['http'], array());
        $this->assertIdentical($this->schema->defaults['http'], array());
    }
    
    function test_define() {
        $this->schema->addNamespace('Car');
        
        $this->schema->add('Car', 'Seats', 5, 'int', false);
        
        $this->assertIdentical($this->schema->defaults['Car']['Seats'], 5);
        $this->assertIdentical($this->schema->info['Car']['Seats'],
            new HTMLPurifier_ConfigDef_Directive('int')
        );
        
        $this->schema->add('Car', 'Age', null, 'int', true);
        
        $this->assertIdentical($this->schema->defaults['Car']['Age'], null);
        $this->assertIdentical($this->schema->info['Car']['Age'], 
            new HTMLPurifier_ConfigDef_Directive('int', true)
        );
        
    }
    
    function test_defineAllowedValues() {
        $this->schema->addNamespace('QuantumNumber', 'D');
        $this->schema->add('QuantumNumber', 'Spin', 0.5, 'float', false);
        $this->schema->add('QuantumNumber', 'Current', 's', 'string', false);
        $this->schema->add('QuantumNumber', 'Difficulty', null, 'string', true);
        
        $this->schema->addAllowedValues( // okay, since default is null
            'QuantumNumber', 'Difficulty', array('easy' => true, 'medium' => true, 'hard' => true)
        );
        
        $this->assertIdentical($this->schema->defaults['QuantumNumber']['Difficulty'], null);
        $this->assertIdentical($this->schema->info['QuantumNumber']['Difficulty'], 
            new HTMLPurifier_ConfigDef_Directive(
                'string',
                true,
                array(
                    'easy' => true,
                    'medium' => true,
                    'hard' => true
                )
            )
        );
        
    }
    
    function test_defineValueAliases() {
        $this->schema->addNamespace('Abbrev', 'Stuff on abbreviations.');
        $this->schema->add('Abbrev', 'HTH', 'Happy to Help', 'string', false);
        $this->schema->addAllowedValues(
            'Abbrev', 'HTH', array(
                'Happy to Help' => true,
                'Hope that Helps' => true,
                'HAIL THE HAND!' => true,
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
        
    }
   
    function testAlias() {
        $this->schema->addNamespace('Home');
        $this->schema->add('Home', 'Rug', 3, 'int', false);
        $this->schema->addAlias('Home', 'Carpet', 'Home', 'Rug');
        
        $this->assertTrue(!isset($this->schema->defaults['Home']['Carpet']));
        $this->assertIdentical($this->schema->info['Home']['Carpet'], 
            new HTMLPurifier_ConfigDef_DirectiveAlias('Home', 'Rug')
        );
        
    }
    
}

