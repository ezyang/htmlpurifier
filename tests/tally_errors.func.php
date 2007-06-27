<?php

function tally_errors($test) {
    // BRITTLE: relies on private code to work
    $context = &SimpleTest::getContext();
    $queue = &$context->get('SimpleErrorQueue');
    if (!isset($queue->_expectation_queue)) return; // fut-compat
    foreach ($queue->_expectation_queue as $e) {
        if (count($e) != 2) return; // fut-compat
        if (!isset($e[0])) return; // fut-compat
        $e[0]->_dumper = new SimpleDumper();
        $test->fail('Error expectation not fulfilled: ' .
            $e[0]->testMessage(null));
    }
    $queue->_expectation_queue = array();
}

