<?php

// overload default configuration schema temporarily
$custom_schema = new HTMLPurifier_ConfigSchema();
$old = HTMLPurifier_ConfigSchema::instance();
$custom_schema =& HTMLPurifier_ConfigSchema::instance($custom_schema);

HTMLPurifier_ConfigSchema::defineNamespace('Element', 'Chemical substances that cannot be further decomposed');

HTMLPurifier_ConfigSchema::define('Element', 'Abbr', 'H', 'string', 'Abbreviation of element name.');
HTMLPurifier_ConfigSchema::define('Element', 'Name', 'hydrogen', 'istring', 'Full name of atoms.');
HTMLPurifier_ConfigSchema::define('Element', 'Number', 1, 'int', 'Atomic number, is identity.');
HTMLPurifier_ConfigSchema::define('Element', 'Mass', 1.00794, 'float', 'Atomic mass.');
HTMLPurifier_ConfigSchema::define('Element', 'Radioactive', false, 'bool', 'Does it have rapid decay?');
HTMLPurifier_ConfigSchema::define('Element', 'Isotopes', array('1' => true, '2' => true, '3' => true), 'lookup',
    'What numbers of neutrons for this element have been observed?');
HTMLPurifier_ConfigSchema::define('Element', 'Traits', array('nonmetallic', 'odorless', 'flammable'), 'list',
    'What are general properties of the element?');
HTMLPurifier_ConfigSchema::define('Element', 'IsotopeNames', array('1' => 'protium', '2' => 'deuterium', '3' => 'tritium'), 'hash',
    'Lookup hash of neutron counts to formal names.');

HTMLPurifier_ConfigSchema::defineNamespace('Instrument', 'Of the musical type.');

HTMLPurifier_ConfigSchema::define('Instrument', 'Manufacturer', 'Yamaha', 'string', 'Who made it?');
HTMLPurifier_ConfigSchema::defineAllowedValues('Instrument', 'Manufacturer', array(
    'Yamaha', 'Conn-Selmer', 'Vandoren', 'Laubin', 'Buffet', 'other'));
HTMLPurifier_ConfigSchema::defineValueAliases('Instrument', 'Manufacturer', array(
    'Selmer' => 'Conn-Selmer'));

HTMLPurifier_ConfigSchema::define('Instrument', 'Family', 'woodwind', 'istring', 'What family is it?');
HTMLPurifier_ConfigSchema::defineAllowedValues('Instrument', 'Family', array(
    'brass', 'woodwind', 'percussion', 'string', 'keyboard', 'electronic'));
HTMLPurifier_ConfigSchema::defineValueAliases('Instrument', 'Family', array(
    'synth' => 'electronic'));

HTMLPurifier_ConfigSchema::defineNamespace('ReportCard', 'It is for grades.');
HTMLPurifier_ConfigSchema::define('ReportCard', 'English', null, 'string/null', 'Grade from English class.');
HTMLPurifier_ConfigSchema::define('ReportCard', 'Absences', 0, 'int', 'How many times missing from school?');

HTMLPurifier_ConfigSchema::defineNamespace('Text', 'This stuff is long, boring, and English.');
HTMLPurifier_ConfigSchema::define('Text', 'AboutUs', 'Nothing much, but this should be decently long so that a textarea would be better', 'text', 'Who are we? What are we up to?');
HTMLPurifier_ConfigSchema::define('Text', 'Hash', "not-case-sensitive\nstill-not-case-sensitive\nsuper-not-case-sensitive", 'itext', 'This is of limited utility, but of course it ends up being used.');

