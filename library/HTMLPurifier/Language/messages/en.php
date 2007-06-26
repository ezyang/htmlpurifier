<?php

$fallback = false;

$messages = array(

'HTMLPurifier' => 'HTML Purifier',
'LanguageFactoryTest: Pizza' => 'Pizza', // for unit testing purposes

'ErrorCollector: No errors' => 'No errors',
'ErrorCollector: At line' => ' at line $line',

'Lexer: Unclosed comment' => 'Unclosed comment',
'Lexer: Unescaped lt' => 'Unescaped less-than sign (<) should be &lt;',
'Lexer: Missing gt' => 'Missing greater-than sign (>), previous less-than sign (<) should be escaped',
'Lexer: Missing attribute key' => 'Attribute declaration has no key',
'Lexer: Missing end quote' => 'Attribute declaration has no end quote',

'Strategy_RemoveForeignElements: Tag transform' => '$1 element transformed into $CurrentToken.Serialized',
'Strategy_RemoveForeignElements: Missing required attribute' => '$1 element missing required attribute $2',
'Strategy_RemoveForeignElements: Foreign element to text' => 'Unrecognized $1 element converted to text',
'Strategy_RemoveForeignElements: Foreign element removed' => 'Unrecognized $1 element removed',
'Strategy_RemoveForeignElements: Comment removed' => 'Comment containing "$1" removed',
'Strategy_RemoveForeignElements: Script removed' => 'Inline scripting removed',
'Strategy_RemoveForeignElements: Token removed to end' => 'Tags and text starting from $1 element where removed to end',


);

$errorNames = array(
    E_ERROR => 'Error',
    E_WARNING => 'Warning',
    E_NOTICE => 'Notice'
);

?>