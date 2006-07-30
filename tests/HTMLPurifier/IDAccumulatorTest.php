<?php

require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_IDAccumulatorTest extends UnitTestCase
{
    
    function test() {
        
        // initialize the accumulator
        $accumulator = new HTMLPurifier_IDAccumulator();
        
        $this->assertTrue( $accumulator->add('id1'));
        $this->assertTrue( $accumulator->add('id2'));
        $this->assertFalse($accumulator->add('id1')); // repeated id
        
    }
    
}

?>