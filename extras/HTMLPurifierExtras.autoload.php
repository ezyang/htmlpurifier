<?php

if (function_exists('spl_autoload_register')) {
  spl_autoload_register(array('HTMLPurifierExtras', 'autoload'));
} elseif (!function_exists('__autoload')) {
  function __autoload($class) {return HTMLPurifierExtras::autoload($class);}
}
