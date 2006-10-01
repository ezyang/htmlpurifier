<?php

require_once 'HTMLPurifier/Context.php';

// mocks
require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_ContextTest extends UnitTestCase
{
    
    var $context;
    
    function setUp() {
        $this->context = new HTMLPurifier_Context();
    }
    
    function testStandardUsage() {
        
        generate_mock_once('HTMLPurifier_IDAccumulator');
        
        $accumulator =& new HTMLPurifier_IDAccumulatorMock($this);
        $this->context->register('IDAccumulator', $accumulator);
        // ...
        $accumulator_2 =& $this->context->get('IDAccumulator');
        $this->assertReference($accumulator, $accumulator_2);
        
        $this->context->destroy('IDAccumulator');
        $accumulator_3 =& $this->context->get('IDAccumulator');
        $this->assertError('Attempted to retrieve non-existent variable');
        $this->assertNull($accumulator_3);
        $this->swallowErrors();
        
        $this->context->destroy('IDAccumulator');
        $this->assertError('Attempted to destroy non-existent variable');
        $this->swallowErrors();
        
    }
    
    function testReRegister() {
        
        $var = true;
        $this->context->register('OnceOnly', $var);
        $this->assertNoErrors();
        
        $this->context->register('OnceOnly', $var);
        $this->assertError('Name collision, cannot re-register');
        $this->swallowErrors();
        
        // destroy it, now registration is okay
        $this->context->destroy('OnceOnly');
        $this->context->register('OnceOnly', $var);
        $this->assertNoErrors();
        
    }
    
}

?>