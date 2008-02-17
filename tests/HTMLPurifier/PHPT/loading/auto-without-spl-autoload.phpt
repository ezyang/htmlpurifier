--TEST--
HTMLPurifier.auto.php without spl_autoload_register without userland autoload loading test
--SKIPIF--
<?php
if (function_exists('spl_autoload_register')) {
    echo "skip - spl_autoload_register() available";
}
--FILE--
<?php
assert("!function_exists('__autoload')");
require_once '../library/HTMLPurifier.auto.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
echo $purifier->purify('<b>Salsa!') . PHP_EOL;
assert("function_exists('__autoload')");

--EXPECT--
<b>Salsa!</b>

