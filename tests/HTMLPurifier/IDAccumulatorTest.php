<?php

require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_IDAccumulatorTest extends UnitTestCase
{
    
    function test() {
        
        // initialize the accumulator
        $accumulator = HTMLPurifier_IDAccumulator::instance();
        $accumulator->reset();
        
        $this->assertTrue( $accumulator->add('id1'));
        $this->assertTrue( $accumulator->add('id2'));
        $this->assertFalse($accumulator->add('id1')); // repeated id
        $accumulator->reset();
        
        $this->assertTrue( $accumulator->add('id2')); // test reset
        $accumulator->reset();
        
    }
    
}

?>