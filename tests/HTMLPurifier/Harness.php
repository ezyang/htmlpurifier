<?php

require_once 'HTMLPurifier/URIParser.php';

/**
 * All-use harness, use this rather than SimpleTest's
 */
class HTMLPurifier_Harness extends UnitTestCase
{
    
    function HTMLPurifier_Harness() {
        parent::UnitTestCase();
    }
    
    var $config, $context;
    
    /**
     * Generates easily accessible default config/context
     */
    function setUp() {
        list($this->config, $this->context) = $this->createCommon();
    }
    
    /**
     * Accepts config and context and prepares them into a valid state
     * @param &$config Reference to config variable
     * @param &$context Reference to context variable
     */
    function prepareCommon(&$config, &$context) {
        $config = HTMLPurifier_Config::create($config);
        if (!$context) $context = new HTMLPurifier_Context();
    }
    
    /**
     * Generates default configuration and context objects
     * @return Defaults in form of array($config, $context)
     */
    function createCommon() {
        return array(HTMLPurifier_Config::createDefault(), new HTMLPurifier_Context);
    }
    
    /**
     * If $expect is false, ignore $result and check if status failed.
     * Otherwise, check if $status if true and $result === $expect.
     * @param $status Boolean status
     * @param $result Mixed result from processing
     * @param $expect Mixed expectation for result
     */
    function assertEitherFailOrIdentical($status, $result, $expect) {
        if ($expect === false) {
            $this->assertFalse($status, 'Expected false result, got true');
        } else {
            $this->assertTrue($status, 'Expected true result, got false');
            $this->assertIdentical($result, $expect);
        }
    }
    
    function getTests() {
        // __onlytest makes only one test get triggered
        foreach (get_class_methods(get_class($this)) as $method) {
            if (strtolower(substr($method, 0, 10)) == '__onlytest') {
                return array($method);
            }
        }
        return parent::getTests();
    }
    
}

