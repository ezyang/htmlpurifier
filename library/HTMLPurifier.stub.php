<?php

require_once 'HTMLPurifier.includes.php';

$config = HTMLPurifier_Config::createDefault();
if (isset($argv[1])) $config->loadIni($argv[1]);
$purifier = new HTMLPurifier($config);
echo $purifier->purify(file_get_contents('php://stdin'));
