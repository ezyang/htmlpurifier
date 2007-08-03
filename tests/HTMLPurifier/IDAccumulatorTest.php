<?php

require_once 'HTMLPurifier/IDAccumulator.php';

class HTMLPurifier_IDAccumulatorTest extends HTMLPurifier_Harness
{
    
    function test() {
        
        // initialize the accumulator
        $accumulator = new HTMLPurifier_IDAccumulator();
        
        $this->assertTrue( $accumulator->add('id1'));
        $this->assertTrue( $accumulator->add('id2'));
        $this->assertFalse($accumulator->add('id1')); // repeated id
        
        // you can also access the properties (they're public)
        $this->assertTrue( isset($accumulator->ids['id2']) );
        
    }
    
    function testLoad() {
        
        $accumulator = new HTMLPurifier_IDAccumulator();
        
        $accumulator->load(array('id1', 'id2', 'id3'));
        
        $this->assertFalse($accumulator->add('id1')); // repeated id
        $this->assertTrue($accumulator->add('id4'));
        
    }
    
}

