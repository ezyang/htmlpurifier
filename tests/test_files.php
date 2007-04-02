<?php

if (!defined('HTMLPurifierTest')) exit;

// define callable test files (sorted alphabetically)
$test_files[] = 'AttrDef/CSS/BackgroundPositionTest.php';
$test_files[] = 'AttrDef/CSS/BackgroundTest.php';
$test_files[] = 'AttrDef/CSS/BorderTest.php';
$test_files[] = 'AttrDef/CSS/ColorTest.php';
$test_files[] = 'AttrDef/CSS/CompositeTest.php';
$test_files[] = 'AttrDef/CSS/FontFamilyTest.php';
$test_files[] = 'AttrDef/CSS/FontTest.php';
$test_files[] = 'AttrDef/CSS/LengthTest.php';
$test_files[] = 'AttrDef/CSS/ListStyleTest.php';
$test_files[] = 'AttrDef/CSS/MultipleTest.php';
$test_files[] = 'AttrDef/CSS/NumberTest.php';
$test_files[] = 'AttrDef/CSS/PercentageTest.php';
$test_files[] = 'AttrDef/CSS/TextDecorationTest.php';
$test_files[] = 'AttrDef/CSS/URITest.php';
$test_files[] = 'AttrDef/CSSTest.php';
$test_files[] = 'AttrDef/EnumTest.php';
$test_files[] = 'AttrDef/HTML/IDTest.php';
$test_files[] = 'AttrDef/HTML/LengthTest.php';
$test_files[] = 'AttrDef/HTML/MultiLengthTest.php';
$test_files[] = 'AttrDef/HTML/NmtokensTest.php';
$test_files[] = 'AttrDef/HTML/PixelsTest.php';
$test_files[] = 'AttrDef/HTML/LinkTypesTest.php';
$test_files[] = 'AttrDef/IntegerTest.php';
$test_files[] = 'AttrDef/LangTest.php';
$test_files[] = 'AttrDef/TextTest.php';
$test_files[] = 'AttrDef/URI/Email/SimpleCheckTest.php';
$test_files[] = 'AttrDef/URI/HostTest.php';
$test_files[] = 'AttrDef/URI/IPv4Test.php';
$test_files[] = 'AttrDef/URI/IPv6Test.php';
$test_files[] = 'AttrDef/URITest.php';
$test_files[] = 'AttrDefTest.php';
$test_files[] = 'AttrTransform/BdoDirTest.php';
$test_files[] = 'AttrTransform/BgColorTest.php';
$test_files[] = 'AttrTransform/BorderTest.php';
$test_files[] = 'AttrTransform/ImgRequiredTest.php';
$test_files[] = 'AttrTransform/LangTest.php';
$test_files[] = 'AttrTransform/LengthTest.php';
$test_files[] = 'AttrTransform/NameTest.php';
$test_files[] = 'AttrTransform/TextAlignTest.php';
$test_files[] = 'ChildDef/ChameleonTest.php';
$test_files[] = 'ChildDef/CustomTest.php';
$test_files[] = 'ChildDef/OptionalTest.php';
$test_files[] = 'ChildDef/RequiredTest.php';
$test_files[] = 'ChildDef/StrictBlockquoteTest.php';
$test_files[] = 'ChildDef/TableTest.php';
$test_files[] = 'ConfigSchemaTest.php';
$test_files[] = 'ConfigTest.php';
$test_files[] = 'ContextTest.php';
$test_files[] = 'EncoderTest.php';
$test_files[] = 'EntityLookupTest.php';
$test_files[] = 'EntityParserTest.php';
$test_files[] = 'GeneratorTest.php';
$test_files[] = 'HTMLModuleManagerTest.php';
$test_files[] = 'IDAccumulatorTest.php';
$test_files[] = 'LanguageFactoryTest.php';
$test_files[] = 'LanguageTest.php';
$test_files[] = 'Lexer/DirectLexTest.php';
$test_files[] = 'LexerTest.php';
$test_files[] = 'PercentEncoderTest.php';
$test_files[] = 'Strategy/CompositeTest.php';
$test_files[] = 'Strategy/CoreTest.php';
$test_files[] = 'Strategy/FixNestingTest.php';
$test_files[] = 'Strategy/MakeWellFormedTest.php';
$test_files[] = 'Strategy/RemoveForeignElementsTest.php';
$test_files[] = 'Strategy/ValidateAttributesTest.php';
$test_files[] = 'TagTransformTest.php';
$test_files[] = 'Test.php';
$test_files[] = 'TokenTest.php';
$test_files[] = 'URISchemeRegistryTest.php';
$test_files[] = 'URISchemeTest.php';

if (version_compare(PHP_VERSION, '5', '>=')) {
    $test_files[] = 'TokenFactoryTest.php';
}

?>