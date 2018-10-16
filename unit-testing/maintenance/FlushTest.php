<?php

namespace Tests\Maintenance;

class FlushTest extends \PHPUnit_Framework_TestCase
{
    /**
     * ensures that function e in flush.php doesn't run any other files that the defined files
     */
    public function testEFunction()
    {
        // test the return type of flush command
        $php = PHP_BINARY;
        $flushFile = __DIR__ . '/../../maintenance/flush.php';
        $cmd = $php . ' ' . $flushFile;
        exec($cmd, $output, $return);
        $this->assertEquals(0, $return);

        // test e function will not run any file undefined
        require_once $flushFile;
        $badfile = "maliciousfile.php";
        $result = \e($php, $badfile);
        $output = $this->getActualOutput();
        $expectedOutput = "File " . $badfile . " does not exist.";
        $this->assertContains($expectedOutput, $output);
        $this->assertFalse($result);
    }
}
