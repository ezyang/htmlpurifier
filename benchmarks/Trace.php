<?php

ini_set('xdebug.trace_format', 1);
ini_set('xdebug.show_mem_delta', true);

xdebug_start_trace(dirname(__FILE__) . '/Trace');
require_once '../library/HTMLPurifier.auto.php';

$purifier = new HTMLPurifier();

$data = $purifier->purify(file_get_contents('samples/Lexer/4.html'));
xdebug_stop_trace();
