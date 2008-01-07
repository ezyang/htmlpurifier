<?php

/**
 * Implements an external test-case like RemoteTestCase that parses its
 * output from XML returned by a command line call
 */
class CliTestCase
{
    public $_command;
    public $_out = false;
    public $_quiet = false;
    public $_errors = array();
    /**
     * @param $command Command to execute to retrieve XML
     * @param $xml Whether or not to suppress error messages
     */
    public function __construct($command, $quiet = false) {
        $this->_command = $command;
        $this->_quiet = $quiet;
    }
    public function getLabel() {
        return $this->_command;
    }
    public function run(&$reporter) {
        if (!$this->_quiet) $reporter->paintFormattedMessage('Running ['.$this->_command.']');
        $xml = shell_exec($this->_command);
        if (! $xml) {
            if (!$this->_quiet) {
                trigger_error('Command did not have any output [' . $this->_command . ']');
            }
            return false;
        }
        $parser = &$this->_createParser($reporter);
        
        set_error_handler(array($this, '_errorHandler'));
        $status = $parser->parse($xml);
        restore_error_handler();
        
        if (! $status) {
            if (!$this->_quiet) {
                foreach ($this->_errors as $error) {
                    list($no, $str, $file, $line) = $error;
                    $reporter->paintFormattedMessage("Error $no: $str on line $line of $file");
                }
                $msg = "Command produced malformed XML: \n";
                if (strlen($xml) > 120) {
                    $msg .= substr($xml, 0, 50) . "...\n\n[snip]\n\n..." . substr($xml, -50);
                } else {
                    $msg .= $xml;
                }
                $reporter->paintFormattedMessage($msg);
            }
            return false;
        }
        return true;
    }
    public function &_createParser(&$reporter) {
        $parser = new SimpleTestXmlParser($reporter);
        return $parser;
    }
    public function getSize() {
        return 1; // we don't know it
    }
    public function _errorHandler($a, $b, $c, $d) {
        $this->_errors[] = array($a, $b, $c, $d); // see set_error_handler()
    }
}

