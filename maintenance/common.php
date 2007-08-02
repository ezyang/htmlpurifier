<?php

function assertCli() {
    if (php_sapi_name() != 'cli' && !getenv('PHP_IS_CLI')) {
        echo 'Script cannot be called from web-browser (if you are calling via cli,
set environment variable PHP_IS_CLI to work around this).';
        exit;
    }
}
