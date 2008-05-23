<?php

require_once '../library/HTMLPurifier.path.php';
require_once 'HTMLPurifier.includes.php';

$begin = xdebug_memory_usage();

$schema = HTMLPurifier_ConfigSchema::makeFromSerial();

echo xdebug_memory_usage() - $begin;
