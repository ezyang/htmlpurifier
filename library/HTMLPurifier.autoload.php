<?php

if (function_exists('spl_autoload_register')) {
  spl_autoload_register(array('HTMLPurifier_Bootstrap', 'autoload'));
} elseif (!function_exists('__autoload')) {
  function __autoload($class) {return HTMLPurifier_Bootstrap::autoload($class);}
}
