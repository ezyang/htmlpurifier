<?php

namespace HTMLPurifier;

require_once(__DIR__ . '/' . 'HTMLPurifier/Bootstrap.php');

// register our autoloader in a modern way..
spl_autoload_register(function($class){
	return \HTMLPurifier_Bootstrap::autoload($class);
});

class Processor extends \HTMLPurifier {
	
	// now we can use this library without shenanigans..
	
}
