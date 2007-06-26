<?php

$fallback = false;

$messages = array(

'HTMLPurifier' => 'HTML Purifier',
'LanguageFactoryTest: Pizza' => 'Pizza', // for unit testing purposes

'ErrorCollector: No errors' => 'No errors detected. However, because error reporting is still incomplete, there may have been errors that the error collector was not notified of; please inspect the output HTML carefully.',
'ErrorCollector: At line' => ' at line $line',

'Lexer: Unclosed comment' => 'Unclosed comment',
'Lexer: Unescaped lt' => 'Unescaped less-than sign (<) should be &lt;',
'Lexer: Missing gt' => 'Missing greater-than sign (>), previous less-than sign (<) should be escaped',
'Lexer: Missing attribute key' => 'Attribute declaration has no key',
'Lexer: Missing end quote' => 'Attribute declaration has no end quote',

'Strategy_RemoveForeignElements: Tag transform' => '<$1> element transformed into $CurrentToken.Serialized',
'Strategy_RemoveForeignElements: Missing required attribute' => '$CurrentToken.Compact element missing required attribute $1',
'Strategy_RemoveForeignElements: Foreign element to text' => 'Unrecognized $CurrentToken.Serialized tag converted to text',
'Strategy_RemoveForeignElements: Foreign element removed' => 'Unrecognized $CurrentToken.Serialized tag removed',
'Strategy_RemoveForeignElements: Comment removed' => 'Comment containing "$CurrentToken.Data" removed',
'Strategy_RemoveForeignElements: Script removed' => 'Script removed',
'Strategy_RemoveForeignElements: Token removed to end' => 'Tags and text starting from $1 element where removed to end',

'Strategy_MakeWellFormed: Unnecessary end tag removed' => 'Unnecessary $CurrentToken.Serialized tag removed',
'Strategy_MakeWellFormed: Unnecessary end tag to text' => 'Unnecessary $CurrentToken.Serialized tag converted to text',
'Strategy_MakeWellFormed: Tag auto closed' => '$1.Compact started on line $1.Line auto-closed by $CurrentToken.Compact',
'Strategy_MakeWellFormed: Stray end tag removed' => 'Stray $CurrentToken.Serialized tag removed',
'Strategy_MakeWellFormed: Stray end tag to text' => 'Stray $CurrentToken.Serialized tag converted to text',
'Strategy_MakeWellFormed: Tag closed by element end' => '$1.Compact tag started on line $1.Line closed by end of $CurrentToken.Serialized',
'Strategy_MakeWellFormed: Tag closed by document end' => '$1.Compact tag started on line $1.Line closed by end of document',

);

$errorNames = array(
    E_ERROR => 'Error',
    E_WARNING => 'Warning',
    E_NOTICE => 'Notice'
);

?>