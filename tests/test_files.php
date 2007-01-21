<?php

if (!defined('HTMLPurifierTest')) exit;

// define callable test files
$test_files[] = 'ConfigTest.php';
$test_files[] = 'ConfigSchemaTest.php';
$test_files[] = 'LexerTest.php';
$test_files[] = 'Lexer/DirectLexTest.php';
$test_files[] = 'TokenTest.php';
$test_files[] = 'ChildDef/RequiredTest.php';
$test_files[] = 'ChildDef/OptionalTest.php';
$test_files[] = 'ChildDef/ChameleonTest.php';
$test_files[] = 'ChildDef/CustomTest.php';
$test_files[] = 'ChildDef/TableTest.php';
$test_files[] = 'ChildDef/StrictBlockquoteTest.php';
$test_files[] = 'GeneratorTest.php';
$test_files[] = 'EntityLookupTest.php';
$test_files[] = 'Strategy/RemoveForeignElementsTest.php';
$test_files[] = 'Strategy/MakeWellFormedTest.php';
$test_files[] = 'Strategy/FixNestingTest.php';
$test_files[] = 'Strategy/CompositeTest.php';
$test_files[] = 'Strategy/CoreTest.php';
$test_files[] = 'Strategy/ValidateAttributesTest.php';
$test_files[] = 'AttrDefTest.php';
$test_files[] = 'AttrDef/EnumTest.php';
$test_files[] = 'AttrDef/IDTest.php';
$test_files[] = 'AttrDef/ClassTest.php';
$test_files[] = 'AttrDef/TextTest.php';
$test_files[] = 'AttrDef/LangTest.php';
$test_files[] = 'AttrDef/PixelsTest.php';
$test_files[] = 'AttrDef/LengthTest.php';
$test_files[] = 'AttrDef/URITest.php';
$test_files[] = 'AttrDef/CSSTest.php';
$test_files[] = 'AttrDef/CompositeTest.php';
$test_files[] = 'AttrDef/ColorTest.php';
$test_files[] = 'AttrDef/IntegerTest.php';
$test_files[] = 'AttrDef/NumberTest.php';
$test_files[] = 'AttrDef/CSSLengthTest.php';
$test_files[] = 'AttrDef/PercentageTest.php';
$test_files[] = 'AttrDef/MultipleTest.php';
$test_files[] = 'AttrDef/TextDecorationTest.php';
$test_files[] = 'AttrDef/FontFamilyTest.php';
$test_files[] = 'AttrDef/HostTest.php';
$test_files[] = 'AttrDef/IPv4Test.php';
$test_files[] = 'AttrDef/IPv6Test.php';
$test_files[] = 'AttrDef/FontTest.php';
$test_files[] = 'AttrDef/BorderTest.php';
$test_files[] = 'AttrDef/ListStyleTest.php';
$test_files[] = 'AttrDef/Email/SimpleCheckTest.php';
$test_files[] = 'AttrDef/CSSURITest.php';
$test_files[] = 'AttrDef/BackgroundPositionTest.php';
$test_files[] = 'AttrDef/BackgroundTest.php';
$test_files[] = 'IDAccumulatorTest.php';
$test_files[] = 'TagTransformTest.php';
$test_files[] = 'AttrTransform/LangTest.php';
$test_files[] = 'AttrTransform/TextAlignTest.php';
$test_files[] = 'AttrTransform/BdoDirTest.php';
$test_files[] = 'AttrTransform/ImgRequiredTest.php';
$test_files[] = 'URISchemeRegistryTest.php';
$test_files[] = 'URISchemeTest.php';
$test_files[] = 'EncoderTest.php';
$test_files[] = 'EntityParserTest.php';
$test_files[] = 'Test.php';
$test_files[] = 'ContextTest.php';
$test_files[] = 'PercentEncoderTest.php';

if (version_compare(PHP_VERSION, '5', '>=')) {
    $test_files[] = 'TokenFactoryTest.php';
}

?>