<?php

if (!defined('HTMLPurifierTest')) exit;

// define callable test files (sorted alphabetically)

// HTML Purifier main library

$test_files[] = 'HTMLPurifier/AttrCollectionsTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/BackgroundPositionTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/BackgroundTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/BorderTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/ColorTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/CompositeTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/FontFamilyTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/FontTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/LengthTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/ListStyleTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/MultipleTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/NumberTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/PercentageTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/TextDecorationTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSS/URITest.php';
$test_files[] = 'HTMLPurifier/AttrDef/CSSTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/EnumTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/ColorTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/IDTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/LengthTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/FrameTargetTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/MultiLengthTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/NmtokensTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/PixelsTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/HTML/LinkTypesTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/IntegerTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/LangTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/TextTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/URI/Email/SimpleCheckTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/URI/HostTest.php';
$test_files[] = 'HTMLPurifier/AttrDef/URI/IPv4Test.php';
$test_files[] = 'HTMLPurifier/AttrDef/URI/IPv6Test.php';
$test_files[] = 'HTMLPurifier/AttrDef/URITest.php';
$test_files[] = 'HTMLPurifier/AttrDefTest.php';
$test_files[] = 'HTMLPurifier/AttrTransformTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/BdoDirTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/BgColorTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/BoolToCSSTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/BorderTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/EnumToCSSTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/ImgRequiredTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/ImgSpaceTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/LangTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/LengthTest.php';
$test_files[] = 'HTMLPurifier/AttrTransform/NameTest.php';
$test_files[] = 'HTMLPurifier/AttrTypesTest.php';
$test_files[] = 'HTMLPurifier/AttrValidator_ErrorsTest.php';
$test_files[] = 'HTMLPurifier/ChildDef/ChameleonTest.php';
$test_files[] = 'HTMLPurifier/ChildDef/CustomTest.php';
$test_files[] = 'HTMLPurifier/ChildDef/OptionalTest.php';
$test_files[] = 'HTMLPurifier/ChildDef/RequiredTest.php';
$test_files[] = 'HTMLPurifier/ChildDef/StrictBlockquoteTest.php';
$test_files[] = 'HTMLPurifier/ChildDef/TableTest.php';
$test_files[] = 'HTMLPurifier/ConfigSchemaTest.php';
$test_files[] = 'HTMLPurifier/ConfigTest.php';
$test_files[] = 'HTMLPurifier/ContextTest.php';
$test_files[] = 'HTMLPurifier/DefinitionCacheFactoryTest.php';
$test_files[] = 'HTMLPurifier/DefinitionCacheTest.php';
$test_files[] = 'HTMLPurifier/DefinitionCache/Decorator/CleanupTest.php';
$test_files[] = 'HTMLPurifier/DefinitionCache/Decorator/MemoryTest.php';
$test_files[] = 'HTMLPurifier/DefinitionCache/DecoratorTest.php';
$test_files[] = 'HTMLPurifier/DefinitionCache/SerializerTest.php';
$test_files[] = 'HTMLPurifier/DefinitionTest.php';
$test_files[] = 'HTMLPurifier/DoctypeRegistryTest.php';
$test_files[] = 'HTMLPurifier/ElementDefTest.php';
$test_files[] = 'HTMLPurifier/ErrorCollectorTest.php';
$test_files[] = 'HTMLPurifier/EncoderTest.php';
$test_files[] = 'HTMLPurifier/EntityLookupTest.php';
$test_files[] = 'HTMLPurifier/EntityParserTest.php';
$test_files[] = 'HTMLPurifier/GeneratorTest.php';
$test_files[] = 'HTMLPurifier/HTMLDefinitionTest.php';
$test_files[] = 'HTMLPurifier/HTMLModuleManagerTest.php';
$test_files[] = 'HTMLPurifier/HTMLModuleTest.php';
$test_files[] = 'HTMLPurifier/HTMLModule/RubyTest.php';
$test_files[] = 'HTMLPurifier/HTMLModule/ScriptingTest.php';
$test_files[] = 'HTMLPurifier/HTMLModule/TidyTest.php';
$test_files[] = 'HTMLPurifier/IDAccumulatorTest.php';
$test_files[] = 'HTMLPurifier/Injector/AutoParagraphTest.php';
$test_files[] = 'HTMLPurifier/Injector/LinkifyTest.php';
$test_files[] = 'HTMLPurifier/Injector/PurifierLinkifyTest.php';
$test_files[] = 'HTMLPurifier/LanguageFactoryTest.php';
$test_files[] = 'HTMLPurifier/LanguageTest.php';
$test_files[] = 'HTMLPurifier/Lexer/DirectLexTest.php';
$test_files[] = 'HTMLPurifier/Lexer/DirectLex_ErrorsTest.php';
$test_files[] = 'HTMLPurifier/LexerTest.php';
$test_files[] = 'HTMLPurifier/PercentEncoderTest.php';
$test_files[] = 'HTMLPurifier/Strategy/CompositeTest.php';
$test_files[] = 'HTMLPurifier/Strategy/CoreTest.php';
$test_files[] = 'HTMLPurifier/Strategy/FixNestingTest.php';
$test_files[] = 'HTMLPurifier/Strategy/FixNesting_ErrorsTest.php';
$test_files[] = 'HTMLPurifier/Strategy/MakeWellFormedTest.php';
$test_files[] = 'HTMLPurifier/Strategy/MakeWellFormed_ErrorsTest.php';
$test_files[] = 'HTMLPurifier/Strategy/RemoveForeignElementsTest.php';
$test_files[] = 'HTMLPurifier/Strategy/RemoveForeignElements_ErrorsTest.php';
$test_files[] = 'HTMLPurifier/Strategy/ValidateAttributesTest.php';
$test_files[] = 'HTMLPurifier/TagTransformTest.php';
$test_files[] = 'HTMLPurifier/TokenTest.php';
$test_files[] = 'HTMLPurifier/URIDefinitionTest.php';
$test_files[] = 'HTMLPurifier/URIFilter/DisableExternalTest.php';
$test_files[] = 'HTMLPurifier/URIFilter/DisableExternalResourcesTest.php';
$test_files[] = 'HTMLPurifier/URIFilter/HostBlacklistTest.php';
$test_files[] = 'HTMLPurifier/URIFilter/MakeAbsoluteTest.php';
$test_files[] = 'HTMLPurifier/URIParserTest.php';
$test_files[] = 'HTMLPurifier/URISchemeRegistryTest.php';
$test_files[] = 'HTMLPurifier/URISchemeTest.php';
$test_files[] = 'HTMLPurifier/URITest.php';
$test_files[] = 'HTMLPurifierTest.php';

if (version_compare(PHP_VERSION, '5', '>=')) {
    $test_files[] = 'HTMLPurifier/TokenFactoryTest.php';
}

// ConfigDoc auxiliary library

// ... none yet

