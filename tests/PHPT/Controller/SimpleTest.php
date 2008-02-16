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
        $suite = new PHPT_Suite(array($this->_path));
        $phpt_reporter = new PHPT_Reporter_SimpleTest($this->_reporter);
        $suite->run($phpt_reporter);
    }
    
}
