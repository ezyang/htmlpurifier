<?php

require_once('HTMLPurifier/Strategy.php');
require_once('HTMLPurifier/Strategy/Composite.php');

class HTMLPurifier_Strategy_Composite_Test
    extends HTMLPurifier_Strategy_Composite
{
    
    function HTMLPurifier_Strategy_Composite_Test(&$strategies) {
        $this->strategies =& $strategies;
    }
    
}

// doesn't use Strategy harness
class HTMLPurifier_Strategy_CompositeTest extends UnitTestCase
{
    
    function test() {
        
        generate_mock_once('HTMLPurifier_Strategy');
        
        // setup a bunch of mock strategies to inject into our composite test
        
        $mock_1 = new HTMLPurifier_StrategyMock($this);
        $mock_2 = new HTMLPurifier_StrategyMock($this);
        $mock_3 = new HTMLPurifier_StrategyMock($this);
        
        // setup the object
        
        $strategies = array($mock_1, $mock_2, $mock_3);
        $composite = new HTMLPurifier_Strategy_Composite_Test($strategies);
        
        // setup expectations
        
        $input_1 = 'This is raw data';
        $input_2 = 'Processed by 1';
        $input_3 = 'Processed by 1 and 2';
        $input_4 = 'Processed by 1, 2 and 3'; // expected output
        
        $mock_1->expectOnce('execute', array($input_1));
        $mock_1->setReturnValue('execute', $input_2, array($input_1));
        
        $mock_2->expectOnce('execute', array($input_2));
        $mock_2->setReturnValue('execute', $input_3, array($input_2));
        
        $mock_3->expectOnce('execute', array($input_3));
        $mock_3->setReturnValue('execute', $input_4, array($input_3));
        
        // perform test
        
        $output = $composite->execute($input_1);
        $this->assertIdentical($input_4, $output);
        
        // tally the calls
        
        $mock_1->tally();
        $mock_2->tally();
        $mock_3->tally();
        
    }
    
}

?>