<?php

require_once 'HTMLPurifier.includes.php';

$config = HTMLPurifier_Config::createDefault();
$config->set('Core.LexerImpl', 'DirectLex');
for ($i = 1, $c = count($argv); $i < $c; $i += 2) {
    $config->set($argv[$i], $argv[$i+1]);
}
$purifier = new HTMLPurifier($config);
echo $purifier->purify(file_get_contents('php://stdin'));
