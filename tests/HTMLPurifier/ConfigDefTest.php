<?php

require_once 'HTMLPurifier/ConfigDef.php';

class HTMLPurifier_ConfigDefTest extends UnitTestCase
{
    
    var $old_copy;
    var $our_copy;
    
    function setUp() {
        // yes, I know this is slightly convoluted, but that's the price
        // you pay for using Singletons. Good thing we can overload it.
        
        // first, let's get a clean copy to do tests
        $our_copy = new HTMLPurifier_ConfigDef();
        // get the old copy
        $this->old_copy = HTMLPurifier_ConfigDef::instance();
        // put in our copy, and reassign to the REAL reference
        $this->our_copy =& HTMLPurifier_ConfigDef::instance($our_copy);
    }
    
    function tearDown() {
        // testing is done, restore the old copy
        HTMLPurifier_ConfigDef::instance($this->old_copy);
    }
    
    function testNormal() {
        
        $file = $this->our_copy->mungeFilename(__FILE__);
        
        // define a namespace
        $description = 'Configuration that is always available.';
        HTMLPurifier_ConfigDef::defineNamespace(
            'Core', $description
        ); $line = __LINE__;
        $this->assertIdentical($this->our_copy->defaults, array(
            'Core' => array()
        ));
        $this->assertIdentical($this->our_copy->info, array(
            'Core' => array()
        ));
        $namespace = new HTMLPurifier_ConfigEntity_Namespace();
        $namespace->addDescription($file, $line, $description);
        $this->assertIdentical($this->our_copy->info_namespace, array(
            'Core' => $namespace
        ));
        
        
        
        // define a directive
        $description = 'This is a description of the directive.';
        HTMLPurifier_ConfigDef::define(
            'Core', 'Name', 'default value', 'string',
            $description
        ); $line = __LINE__;
        $this->assertIdentical($this->our_copy->defaults, array(
            'Core' => array(
                'Name' => 'default value'
            )
        ));
        $directive = new HTMLPurifier_ConfigEntity_Directive();
        $directive->type = 'string';
        $directive->addDescription($file, $line, $description);
        $this->assertIdentical($this->our_copy->info, array(
            'Core' => array(
                'Name' => $directive
            )
        ));
        
        
        
        // define a directive in an undefined namespace
        HTMLPurifier_ConfigDef::define(
            'Extension', 'Name', false, 'bool',
            'This is for an extension, but we have not defined its namespace!'
        );
        $this->assertError('Cannot define directive for undefined namespace');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        
        
        // redefine a value in a valid manner
        $description = 'Alternative configuration definition';
        HTMLPurifier_ConfigDef::define(
            'Core', 'Name', 'default value', 'string',
            $description
        ); $line = __LINE__;
        $this->assertNoErrors();
        $directive->addDescription($file, $line, $description);
        $this->assertIdentical($this->our_copy->info, array(
            'Core' => array(
                'Name' => $directive
            )
        ));
        
        
        
        // redefine a directive in an invalid manner
        HTMLPurifier_ConfigDef::define(
            'Core', 'Name', 'different default', 'string',
            'Inconsistent default or type, cannot redefine'
        );
        $this->assertError('Inconsistent default or type, cannot redefine');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        
        
        // make an enumeration
        HTMLPurifier_ConfigDef::defineAllowedValues(
            'Core', 'Name', array(
                'Real Value',
                'Real Value 2'
            )
        );
        $directive->allowed = array(
            'Real Value' => true,
            'Real Value 2' => true
        );
        $this->assertIdentical($this->our_copy->info, array(
            'Core' => array(
                'Name' => $directive
            )
        ));
        
        
        
        // redefinition of enumeration is cumulative
        HTMLPurifier_ConfigDef::defineAllowedValues(
            'Core', 'Name', array(
                'Real Value 3',
            )
        );
        $directive->allowed['Real Value 3'] = true;
        $this->assertIdentical($this->our_copy->info, array(
            'Core' => array(
                'Name' => $directive
            )
        ));
        
        
        
        // cannot define enumeration for undefined directive
        HTMLPurifier_ConfigDef::defineAllowedValues(
            'Core', 'Foobar', array(
                'Real Value 9',
            )
        );
        $this->assertError('Cannot define allowed values for undefined directive');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        
        
        // test defining value aliases for an enumerated value
        HTMLPurifier_ConfigDef::defineValueAliases(
            'Core', 'Name', array(
                'Aliased Value' => 'Real Value'
            )
        );
        $directive->aliases['Aliased Value'] = 'Real Value';
        $this->assertIdentical($this->our_copy->info, array(
            'Core' => array(
                'Name' => $directive
            )
        ));
        
        
        
        // redefine should be cumulative
        HTMLPurifier_ConfigDef::defineValueAliases(
            'Core', 'Name', array(
                'Aliased Value 2' => 'Real Value 2'
            )
        );
        $directive->aliases['Aliased Value 2'] = 'Real Value 2';
        $this->assertIdentical($this->our_copy->info, array(
            'Core' => array(
                'Name' => $directive
            )
        ));
        
        
        
        // cannot create alias to not-allowed value
        HTMLPurifier_ConfigDef::defineValueAliases(
            'Core', 'Name', array(
                'Aliased Value 3' => 'Invalid Value'
            )
        );
        $this->assertError('Cannot define alias to value that is not allowed');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        
        
        // cannot create alias for already allowed value
        HTMLPurifier_ConfigDef::defineValueAliases(
            'Core', 'Name', array(
                'Real Value' => 'Real Value 2'
            )
        );
        $this->assertError('Cannot define alias over allowed value');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        
        
        // define a directive with an invalid type
        HTMLPurifier_ConfigDef::define(
            'Core', 'Foobar', false, 'omen',
            'Omen is not a valid type, so we reject this.'
        );
        
        $this->assertError('Invalid type for configuration directive');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        
        
        // define a directive with inconsistent type
        HTMLPurifier_ConfigDef::define(
            'Core', 'Foobaz', 10, 'string',
            'If we say string, we should mean it, not integer 10.'
        );
        
        $this->assertError('Default value does not match directive type');
        $this->assertNoErrors();
        $this->swallowErrors();
        
        
        
    }
    
    function assertValid($var, $type, $ret = null) {
        $ret = ($ret === null) ? $var : $ret;
        $this->assertIdentical($this->our_copy->validate($var, $type), $ret);
    }
    
    function assertInvalid($var, $type) {
        $this->assertIdentical($this->our_copy->validate($var, $type), null);
    }
    
    function testValidate() {
        
        $this->assertValid('foobar', 'string');
        $this->assertValid('FOOBAR', 'istring', 'foobar');
        $this->assertValid(34, 'int');
        $this->assertValid(3.34, 'float');
        $this->assertValid(false, 'bool');
        $this->assertValid(0, 'bool', false);
        $this->assertValid(1, 'bool', true);
        $this->assertInvalid(34, 'bool');
        $this->assertValid(array('1', '2', '3'), 'list');
        $this->assertValid(array('1' => true, '2' => true), 'lookup');
        $this->assertValid(array('1', '2'), 'lookup', array('1' => true, '2' => true));
        $this->assertValid(array('foo' => 'bar'), 'hash');
        $this->assertInvalid(array(0 => 'moo'), 'hash');
        $this->assertValid(array(1 => 'moo'), 'hash');
        $this->assertValid(23, 'mixed');
        
    }
    
    function assertMungeFilename($oldname, $newname) {
        $this->assertIdentical(
            $this->our_copy->mungeFilename($oldname),
            $newname
        );
    }
    
    function testMungeFilename() {
        
        $this->assertMungeFilename(
            'C:\\php\\libs\\htmlpurifier\\library\\HTMLPurifier\\AttrDef.php',
            'HTMLPurifier/AttrDef.php'
        );
        
        $this->assertMungeFilename(
            'C:\\php\\libs\\htmlpurifier\\library\\HTMLPurifier.php',
            'HTMLPurifier.php'
        );
        
    }
    
}

?>