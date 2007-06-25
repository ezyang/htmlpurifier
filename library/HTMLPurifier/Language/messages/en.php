<?php

$fallback = false;

$messages = array(

'htmlpurifier' => 'HTML Purifier',
'pizza' => 'Pizza', // for unit testing purposes

'ErrorCollector: No errors' => 'No errors',
'ErrorCollector: At line' => ' at line $line',

'Lexer: Unclosed comment' => 'Unclosed comment',
'Lexer: Unescaped lt' => 'Unescaped less-than sign (<) should be &lt;',
'Lexer: Missing gt' => 'Missing greater-than sign (>), previous less-than sign (<) should be escaped',
'Lexer: Missing attribute key' => 'Attribute declaration has no key',
'Lexer: Missing end quote' => 'Attribute declaration has no end quote',

);

$errorNames = array(
    E_ERROR => 'Error',
    E_WARNING => 'Warning',
    E_NOTICE => 'Notice'
);

?>