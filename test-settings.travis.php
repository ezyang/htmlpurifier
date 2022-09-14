<?php

// This file is the configuration for Travis testing.

// Note: The only external library you *need* is SimpleTest; everything else
//       is optional.

// We've got a lot of tests, so we recommend turning the limit off.
set_time_limit(0);

// Turning off output buffering will prevent mysterious errors from core dumps.
$data = @ob_get_clean();
if ($data !== false && $data !== '') {
    echo "Output buffer contains data [".urlencode($data)."]\n";
    exit;
}

// -----------------------------------------------------------------------------
// REQUIRED SETTINGS

// Note on running SimpleTest:
//      You want the Git copy of SimpleTest, found here:
//          https://github.com/simpletest/simpletest/
//
//      If SimpleTest is borked with HTML Purifier, please contact me or
//      the SimpleTest devs; I am a developer for SimpleTest so I should be
//      able to quickly assess a fix. SimpleTest's problem is my problem!

// Where is SimpleTest located? Remember to include a trailing slash!
$simpletest_location = dirname(__FILE__) . '/simpletest/';

// -----------------------------------------------------------------------------
// OPTIONAL SETTINGS

// Note on running PHPT:
//      Vanilla PHPT from https://github.com/tswicegood/PHPT_Core should
//      work fine on Linux w/o multitest.
//
//      To do multitest or Windows testing, you'll need some more
//      patches at https://github.com/ezyang/PHPT_Core
//
//      I haven't tested the Windows setup in a while so I don't know if
//      it still works.

// Should PHPT tests be enabled?
$GLOBALS['HTMLPurifierTest']['PHPT'] = false;

// If PHPT isn't in your Path via PEAR, set that here:
// set_include_path('/path/to/phpt/Core/src' . PATH_SEPARATOR . get_include_path());

// Where is CSSTidy located? (Include trailing slash. Leave false to disable.)
$csstidy_location    = dirname(__FILE__) . '/csstidy/';

// For tests/multitest.php, which versions to test?
$versions_to_test    = array();

// Stable PHP binary to use when invoking maintenance scripts.
$php = 'php';

// For tests/multitest.php, what is the multi-version executable? It must
// accept an extra parameter (version number) before all other arguments
$phpv = false;

// Should PEAR tests be run? If you've got a valid PEAR installation, set this
// to true (or, if it's not in the include path, to its install directory).
$GLOBALS['HTMLPurifierTest']['PEAR'] = false;

// If PEAR is enabled, what PEAR tests should be run? (Note: you will
// need to ensure these libraries are installed)
$GLOBALS['HTMLPurifierTest']['Net_IDNA2'] = true;

// vim: et sw=4 sts=4
