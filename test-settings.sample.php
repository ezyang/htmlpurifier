<?php

// This file is necessary to run the unit tests and profiling
// scripts.

// Is PEAR available on your system? If it isn't, set to false. If PEAR
// is not part of the default include_path, add it.
$GLOBALS['HTMLPurifierTest']['PEAR'] = true;

// How many times should profiling scripts iterate over the function? More runs 
// means more accurate results, but they'll take longer to perform.
$GLOBALS['HTMLPurifierTest']['Runs'] = 2;

// Where is SimpleTest located?
$simpletest_location = '/path/to/simpletest/';

?>