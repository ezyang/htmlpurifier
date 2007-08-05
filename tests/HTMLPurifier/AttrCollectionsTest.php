<?php

require_once 'HTMLPurifier/AttrCollections.php';

class HTMLPurifier_AttrCollectionsTest_NoConstructor extends HTMLPurifier_AttrCollections
{
    function HTMLPurifier_AttrCollectionsTest_NoConstructor() {}
    function expandIdentifiers(&$a, $b) {}
    function performInclusions(&$a) {}
}

class HTMLPurifier_AttrCollectionsTest extends HTMLPurifier_Harness
{
    
    function testConstruction() {
        
        generate_mock_once('HTMLPurifier_AttrTypes');
        
        $collections = new HTMLPurifier_AttrCollectionsTest_NoConstructor();
        
        $types = new HTMLPurifier_AttrTypesMock();
        
        $modules = array();
        
        $modules['Module1'] = new HTMLPurifier_HTMLModule();
        $modules['Module1']->attr_collections = array(
            'Core' => array(
                0 => array('Soup', 'Undefined'),
                'attribute' => 'Type',
                'attribute-2' => 'Type2',
            ),
            'Soup' => array(
                'attribute-3' => 'Type3-old' // overwritten
            )
        );
        
        $modules['Module2'] = new HTMLPurifier_HTMLModule();
        $modules['Module2']->attr_collections = array(
            'Core' => array(
                0 => array('Brocolli')
            ),
            'Soup' => array(
                'attribute-3' => 'Type3'
            ),
            'Brocolli' => array()
        );
        
        $collections->HTMLPurifier_AttrCollections($types, $modules);
        // this is without identifier expansion or inclusions
        $this->assertIdentical(
            $collections->info,
            array(
                'Core' => array(
                    0 => array('Soup', 'Undefined', 'Brocolli'),
                    'attribute' => 'Type',
                    'attribute-2' => 'Type2'
                ),
                'Soup' => array(
                    'attribute-3' => 'Type3'
                ),
                'Brocolli' => array()
            )
        );
        
    }
    
    function test_performInclusions() {
        
        generate_mock_once('HTMLPurifier_AttrTypes');
        
        $types = new HTMLPurifier_AttrTypesMock();
        $collections = new HTMLPurifier_AttrCollections($types, array());
        $collections->info = array(
            'Core' => array(0 => array('Inclusion', 'Undefined'), 'attr-original' => 'Type'),
            'Inclusion' => array(0 => array('SubInclusion'), 'attr' => 'Type'),
            'SubInclusion' => array('attr2' => 'Type')
        );
        
        $collections->performInclusions($collections->info['Core']);
        $this->assertIdentical(
            $collections->info['Core'],
            array(
                'attr-original' => 'Type', 
                'attr' => 'Type',
                'attr2' => 'Type'
            )
        );
        
        // test recursive
        $collections->info = array(
            'One' => array(0 => array('Two'), 'one' => 'Type'),
            'Two' => array(0 => array('One'), 'two' => 'Type')
        );
        $collections->performInclusions($collections->info['One']);
        $this->assertIdentical(
            $collections->info['One'],
            array(
                'one' => 'Type',
                'two' => 'Type'
            )
        );
        
    }
    
    function test_expandIdentifiers() {
        
        generate_mock_once('HTMLPurifier_AttrTypes');
        
        $types = new HTMLPurifier_AttrTypesMock();
        $collections = new HTMLPurifier_AttrCollections($types, array());
        
        $attr = array(
            'attr1' => 'Color',
            'attr2*' => 'URI'
        );
        $c_object = new HTMLPurifier_AttrDef();
        $c_object->_name = 'Color'; // for testing purposes only
        $u_object = new HTMLPurifier_AttrDef();
        $u_object->_name = 'URL'; // for testing purposes only
        
        $types->setReturnValue('get', $c_object, array('Color'));
        $types->setReturnValue('get', $u_object, array('URI'));
        
        $collections->expandIdentifiers($attr, $types);
        
        $u_object->required = true;
        $this->assertIdentical(
            $attr,
            array(
                'attr1' => $c_object,
                'attr2' => $u_object
            )
        );
        
    }
    
}

