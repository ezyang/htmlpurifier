<?php

class HTMLPurifier_AttrTransformHarness extends UnitTestCase
{
    
    var $transform;
    
    function assertTransform($inputs, $expect) {
        foreach ($inputs as $i => $input) {
            $result = $this->transform->transform($input);
            if ($expect[$i] === true) {
                $this->assertEqual($input, $result, "Test $i: %s");
            } else {
                $this->assertEqual($expect[$i], $result, "Test $i: %s");
            }
        }
    }
    
}

?>