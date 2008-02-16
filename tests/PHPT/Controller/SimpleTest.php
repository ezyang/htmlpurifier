<?php

/**
 * Controller for PHPT that implements the SimpleTest unit-testing interface.
 */
class PHPT_Controller_SimpleTest extends SimpleTestCase
{
    
    protected $_path;
    
    public function __construct($path) {
        $this->_path = $path;
        parent::SimpleTestCase($path);
    }
    
    public function testPhpt() {
        if (is_dir($this->_path)) {
            $factory = new PHPT_Suite_Factory();
            $suite = $factory->factory($this->_path, true);
        } else {
            $suite = new PHPT_Suite(array($this->_path));
        }
        // Adapter class that relays messages to SimpleTest
        $phpt_reporter = new PHPT_Reporter_SimpleTest($this->_reporter);
        $suite->run($phpt_reporter);
    }
    
}
