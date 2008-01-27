<?php

/**
 * @note Sample input files are located in the StringHashParser/ directory.
 */
class ConfigSchema_StringHashParserTest extends UnitTestCase
{
    
    /**
     * Instance of ConfigSchema_StringHashParser being tested.
     */
    protected $parser;
    
    function setup() {
        $this->parser = new ConfigSchema_StringHashParser();
    }
    
    /**
     * Assert that $file gets parsed into the form of $expect
     */
    function assertParse($file, $expect) {
        $result = $this->parser->parseFile(dirname(__FILE__) . '/StringHashParser/' . $file);
        $this->assertIdentical($result, $expect);
    }
    
    function testSimple() {
        $this->assertParse('Simple.txt', array(
            'ID' => 'Namespace.Directive',
            'TYPE' => 'string',
            'DESCRIPTION' => "Multiline\nstuff\n"
        ));
    }
    
    function testOverrideSingle() {
        $this->assertParse('OverrideSingle.txt', array(
            'KEY' => 'New',
        ));
    }
    
    function testAppendMultiline() {
        $this->assertParse('AppendMultiline.txt', array(
            'KEY' => "Line1\nLine2\n",
        ));
    }
    
    function testDefault() {
        $this->parser->default = 'NEW-ID';
        $this->assertParse('Default.txt', array(
            'NEW-ID' => 'DefaultValue',
        ));
    }
    
}
