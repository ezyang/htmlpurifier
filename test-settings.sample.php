<?php

// This file is necessary to run the unit tests and profiling scripts.
// Please copy it to 'test-settings.php' and make the necessary edits.

// It's recommended that you turn off PHP's time limit for the unit tests.
set_time_limit(0);

// Turning off output buffering will prevent mysterious errors from core dumps
@ob_end_flush();

// Is PEAR available on your system? If it isn't, set to false. If PEAR
// is not part of the default include_path, add it.
$GLOBALS['HTMLPurifierTest']['PEAR'] = false;
// set_include_path('/path/to/pear' . PATH_SEPARATOR . get_include_path());

// How many times should profiling scripts iterate over the function? More runs 
// means more accurate results, but they'll take longer to perform.
$GLOBALS['HTMLPurifierTest']['Runs'] = 2;

// Where is SimpleTest located?
$simpletest_location = '/path/to/simpletest/';

