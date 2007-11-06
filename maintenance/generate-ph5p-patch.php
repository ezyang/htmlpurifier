<?php

$orig = realpath(dirname(__FILE__) . '/PH5P.php');
$new  = realpath(dirname(__FILE__) . '/../library/HTMLPurifier/Lexer/PH5P.php');
$newt = dirname(__FILE__) . '/PH5P.new.php'; // temporary file

// minor text-processing of new file to get into same format as original
$new_src = file_get_contents($new);
$new_src = '<?php' . PHP_EOL . substr($new_src, strpos($new_src, 'class HTML5 {'));

file_put_contents($newt, $new_src);
shell_exec("diff -u \"$orig\" \"$newt\" > PH5P.patch");
unlink($newt);
