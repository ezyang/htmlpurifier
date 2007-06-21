<?php

// overload default configuration schema temporarily
$custom_schema = new HTMLPurifier_ConfigSchema();
$old = HTMLPurifier_ConfigSchema::instance();
$custom_schema =& HTMLPurifier_ConfigSchema::instance($custom_schema);

if (!class_exists('CS')) {
    class CS extends HTMLPurifier_ConfigSchema {}
}

CS::defineNamespace('Element', 'Chemical substances that cannot be further decomposed');

CS::define('Element', 'Abbr', 'H', 'string', 'Abbreviation of element name.');
CS::define('Element', 'Name', 'hydrogen', 'istring', 'Full name of atoms.');
CS::define('Element', 'Number', 1, 'int', 'Atomic number, is identity.');
CS::define('Element', 'Mass', 1.00794, 'float', 'Atomic mass.');
CS::define('Element', 'Radioactive', false, 'bool', 'Does it have rapid decay?');
CS::define('Element', 'Isotopes', array('1' => true, '2' => true, '3' => true), 'lookup',
    'What numbers of neutrons for this element have been observed?');
CS::define('Element', 'Traits', array('nonmetallic', 'odorless', 'flammable'), 'list',
    'What are general properties of the element?');
CS::define('Element', 'IsotopeNames', array('1' => 'protium', '2' => 'deuterium', '3' => 'tritium'), 'hash',
    'Lookup hash of neutron counts to formal names.');

CS::defineNamespace('Instrument', 'Of the musical type.');

CS::define('Instrument', 'Manufacturer', 'Yamaha', 'string', 'Who made it?');
CS::defineAllowedValues('Instrument', 'Manufacturer', array(
    'Yamaha', 'Conn-Selmer', 'Vandoren', 'Laubin', 'Buffet', 'other'));
CS::defineValueAliases('Instrument', 'Manufacturer', array(
    'Selmer' => 'Conn-Selmer'));

CS::define('Instrument', 'Family', 'woodwind', 'istring', 'What family is it?');
CS::defineAllowedValues('Instrument', 'Family', array(
    'brass', 'woodwind', 'percussion', 'string', 'keyboard', 'electronic'));
CS::defineValueAliases('Instrument', 'Family', array(
    'synth' => 'electronic'));

CS::defineNamespace('ReportCard', 'It is for grades.');
CS::define('ReportCard', 'English', null, 'string/null', 'Grade from English class.');
CS::define('ReportCard', 'Absences', 0, 'int', 'How many times missing from school?');

?>