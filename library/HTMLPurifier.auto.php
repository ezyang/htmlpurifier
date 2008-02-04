<?php

/**
 * This is a stub include that automatically configures the include path.
 * @warning This file is currently broken.
 */

set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path() );
require_once 'HTMLPurifier/Bootstrap.php';
require_once 'HTMLPurifier.autoload.php';

// This is temporary until we get pure autoload working
require_once 'HTMLPurifier.includes.php';
