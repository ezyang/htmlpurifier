<?php

function assertCli() {
    if (php_sapi_name() != 'cli' && !getenv('PHP_IS_CLI')) {
        echo 'Script cannot be called from web-browser (if you are indeed calling via cli,
set environment variable PHP_IS_CLI to work around this).';
        exit(1);
    }
}

// Load useful stuff like FSTools
require_once '../extras/HTMLPurifierExtras.auto.php';
