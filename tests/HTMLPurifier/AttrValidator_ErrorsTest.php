<?php

require_once 'HTMLPurifier/ErrorsHarness.php';
require_once 'HTMLPurifier/AttrValidator.php';

class HTMLPurifier_AttrValidator_ErrorsTest extends HTMLPurifier_ErrorsHarness
{
    
    function invoke($input) {
        $validator = new HTMLPurifier_AttrValidator();
        $validator->validateToken($input, $this->config, $this->context);
    }
    
    function testAttributesTransformedGlobalPre() {
        $this->config->set('HTML', 'DefinitionID',
          'HTMLPurifier_AttrValidator_ErrorsTest::testAttributesTransformedGlobalPre');
        $def =& $this->config->getHTMLDefinition(true);
        generate_mock_once('HTMLPurifier_AttrTransform');
        $transform = new HTMLPurifier_AttrTransformMock();
        $input = array('original' => 'value');
        $output = array('class' => 'value'); // must be valid
        $transform->setReturnValue('transform', $output, array($input, new AnythingExpectation(), new AnythingExpectation()));
        $def->info_attr_transform_pre[] = $transform;
        $this->expectErrorCollection(E_NOTICE, 'AttrValidator: Attributes transformed', $input, $output);
        $token = new HTMLPurifier_Token_Start('span', $input, 1);
        $this->invoke($token);
    }
    
    function testAttributesTransformedLocalPre() {
        $this->config->set('HTML', 'TidyLevel', 'heavy');
        $input = array('align' => 'right');
        $output = array('style' => 'text-align:right;');
        $this->expectErrorCollection(E_NOTICE, 'AttrValidator: Attributes transformed', $input, $output);
        $token = new HTMLPurifier_Token_Start('p', $input, 1);
        $this->invoke($token);
    }
    
    // to lazy to check for global post and global pre
    
    function testAttributeRemoved() {
        $this->expectErrorCollection(E_ERROR, 'AttrValidator: Attribute removed');
        $this->expectContext('CurrentAttr', 'foobar');
        $token = new HTMLPurifier_Token_Start('p', array('foobar' => 'right'), 1);
        $this->expectContext('CurrentToken', $token);
        $this->invoke($token);
    }
    
}

