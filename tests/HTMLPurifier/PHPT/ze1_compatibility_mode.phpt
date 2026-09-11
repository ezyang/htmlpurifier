--TEST--
Error with zend.ze1_compatibility_mode test
--PRESKIPIF--
<?php
if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    echo 'skip - ze1_compatibility_mode not present in PHP 5.3 or later';
}
--INI--
zend.ze1_compatibility_mode = 1
--FILE--
<?php
try {
    require '../library/HTMLPurifier.auto.php';
} catch (Exception $e) {
    echo $e->getMessage();
}
--EXPECTF--
HTML Purifier is not compatible with zend.ze1_compatibility_mode; please turn it off
