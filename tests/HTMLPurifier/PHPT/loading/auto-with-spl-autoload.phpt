--TEST--
HTMLPurifier.auto.php using spl_autoload_register with userland spl_autoload registration loading test
--SKIPIF--
<?php
if (!function_exists('spl_autoload_register')) {
    echo "skip - spl_autoload_register() not available";
}
--FILE--
<?php
function __autoload($class) {
    echo "Autoloading $class..." . PHP_EOL;
    eval("class $class {}");
}

require_once '../library/HTMLPurifier.auto.php';
$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
echo $purifier->purify('<b>Salsa!') . PHP_EOL;

// purposely invoke older autoload
$bar = new Bar();

--EXPECT--
<b>Salsa!</b>
Autoloading Bar...
