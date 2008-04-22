<?php

// overload default configuration schema temporarily
$custom_schema = new HTMLPurifier_ConfigSchema();

$custom_schema->addNamespace('Element', 'Chemical substances that cannot be further decomposed');

$custom_schema->add('Element', 'Abbr', 'H', 'string', false, 'Abbreviation of element name.');
$custom_schema->add('Element', 'Name', 'hydrogen', 'istring', false, 'Full name of atoms.');
$custom_schema->add('Element', 'Number', 1, 'int', false, 'Atomic number, is identity.');
$custom_schema->add('Element', 'Mass', 1.00794, 'float', false, 'Atomic mass.');
$custom_schema->add('Element', 'Radioactive', false, 'bool', false, 'Does it have rapid decay?');
$custom_schema->add('Element', 'Isotopes', array('1' => true, '2' => true, '3' => true), 'lookup', false,
    'What numbers of neutrons for this element have been observed?');
$custom_schema->add('Element', 'Traits', array('nonmetallic', 'odorless', 'flammable'), 'list', false,
    'What are general properties of the element?');
$custom_schema->add('Element', 'IsotopeNames', array('1' => 'protium', '2' => 'deuterium', '3' => 'tritium'), 'hash', false,
    'Lookup hash of neutron counts to formal names.');

$custom_schema->addNamespace('Instrument', 'Of the musical type.');

$custom_schema->add('Instrument', 'Manufacturer', 'Yamaha', 'string', false, 'Who made it?');
$custom_schema->addAllowedValues('Instrument', 'Manufacturer', array(
    'Yamaha', 'Conn-Selmer', 'Vandoren', 'Laubin', 'Buffet', 'other'));
$custom_schema->addValueAliases('Instrument', 'Manufacturer', array(
    'Selmer' => 'Conn-Selmer'));

$custom_schema->add('Instrument', 'Family', 'woodwind', 'istring', false, 'What family is it?');
$custom_schema->addAllowedValues('Instrument', 'Family', array(
    'brass', 'woodwind', 'percussion', 'string', 'keyboard', 'electronic'));
$custom_schema->addValueAliases('Instrument', 'Family', array(
    'synth' => 'electronic'));

$custom_schema->addNamespace('ReportCard', 'It is for grades.');
$custom_schema->add('ReportCard', 'English', null, 'string', true, 'Grade from English class.');
$custom_schema->add('ReportCard', 'Absences', 0, 'int', false, 'How many times missing from school?');

$custom_schema->addNamespace('Text', 'This stuff is long, boring, and English.');
$custom_schema->add('Text', 'AboutUs', 'Nothing much, but this should be decently long so that a textarea would be better', 'text', false, 'Who are we? What are we up to?');
$custom_schema->add('Text', 'Hash', "not-case-sensitive\nstill-not-case-sensitive\nsuper-not-case-sensitive", 'itext', false, 'This is of limited utility, but of course it ends up being used.');

