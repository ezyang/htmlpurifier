--TEST--
HTMLPurifier.auto.php loading test
--FILE--
<?php
require_once '../library/HTMLPurifier.auto.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
echo $purifier->purify('<b>Salsa!');
--EXPECT--
<b>Salsa!</b>
