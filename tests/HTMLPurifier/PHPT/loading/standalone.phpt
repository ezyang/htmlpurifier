--TEST--
HTMLPurifier.standalone.php loading test
--FILE--
<?php
require_once '../library/HTMLPurifier.standalone.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
echo $purifier->purify('<b>Salsa!');
--EXPECT--
<b>Salsa!</b>
