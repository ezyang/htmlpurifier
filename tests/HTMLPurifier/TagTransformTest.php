<?php

require_once 'HTMLPurifier/TagTransform.php';

class HTMLPurifier_TagTransformTest extends UnitTestCase
{
    
    function assertTransformation($transformer,
                                         $name,        $attributes,
                                  $expect_name, $expect_attributes,
                                  $expect_added_attributes = array()) {
        
        
        // start tag transform
        $this->assertEqual(
                new HTMLPurifier_Token_Start($expect_name, $expect_added_attributes),
                $transformer->transform(
                  new HTMLPurifier_Token_Start($name))
            );
        
        // start tag transform with attributes
        $this->assertEqual(
                new HTMLPurifier_Token_Start($expect_name, $expect_attributes),
                $transformer->transform(
                    new HTMLPurifier_Token_Start($name, $attributes)
                )
            );
        
        // end tag transform
        $this->assertEqual(
                new HTMLPurifier_Token_End($expect_name),
                $transformer->transform(new HTMLPurifier_Token_End($name))
            );
        
        // empty tag transform
        $this->assertEqual(
                new HTMLPurifier_Token_Empty($expect_name, $expect_added_attributes),
                $transformer->transform(new HTMLPurifier_Token_Empty($name))
            );
        
        // empty tag transform with attributes
        $this->assertEqual(
                new HTMLPurifier_Token_Empty($expect_name, $expect_attributes),
                $transformer->transform(
                    new HTMLPurifier_Token_Empty($name, $attributes))
            );
        
        
    }
    
    function testSimple() {
        
        $transformer = new HTMLPurifier_TagTransform_Simple('ul');
        
        $this->assertTransformation(
            $transformer,
            'menu', array('class' => 'boom'),
            'ul', array('class' => 'boom')
        );
        
    }
    
    function testCenter() {
        
        $transformer = new HTMLPurifier_TagTransform_Center();
        
        $this->assertTransformation(
            $transformer,
            'center', array('class' => 'boom', 'style'=>'font-weight:bold;'),
            'div', array('class' => 'boom', 'style'=>'text-align:center;font-weight:bold;'),
            array('style'=>'text-align:center;')
        );
        
        // test special case, uppercase attribute key
        $this->assertTransformation(
            $transformer,
            'center', array('STYLE'=>'font-weight:bold;'),
            'div', array('style'=>'text-align:center;font-weight:bold;'),
            array('style'=>'text-align:center;')
        );
        
    }
    
    function assertSizeToStyle($transformer, $size, $style) {
        $this->assertTransformation(
            $transformer,
            'font', array('size' => $size),
            'span', array('style' => 'font-size:' . $style . ';')
        );
    }
    
    function testFont() {
        
        $transformer = new HTMLPurifier_TagTransform_Font();
        
        // test a font-face transformation
        $this->assertTransformation(
            $transformer,
            'font', array('face' => 'Arial'),
            'span', array('style' => 'font-family:Arial;')
        );
        
        // test a color transformation
        $this->assertTransformation(
            $transformer,
            'font', array('color' => 'red'),
            'span', array('style' => 'color:red;')
        );
        
        // test the size transforms
        $this->assertSizeToStyle($transformer, '1', 'xx-small');
        $this->assertSizeToStyle($transformer, '2', 'small');
        $this->assertSizeToStyle($transformer, '3', 'medium');
        $this->assertSizeToStyle($transformer, '4', 'large');
        $this->assertSizeToStyle($transformer, '5', 'x-large');
        $this->assertSizeToStyle($transformer, '6', 'xx-large');
        $this->assertSizeToStyle($transformer, '7', '300%');
        $this->assertSizeToStyle($transformer, '-1', 'smaller');
        $this->assertSizeToStyle($transformer, '+1', 'larger');
        $this->assertSizeToStyle($transformer, '-2', '60%');
        $this->assertSizeToStyle($transformer, '+2', '150%');
        $this->assertSizeToStyle($transformer, '+4', '300%');
        
        // test multiple transforms, the alphabetical ordering is important
        $this->assertTransformation(
            $transformer,
            'font', array('color' => 'red', 'face' => 'Arial', 'size' => '6'),
            'span', array('style' => 'color:red;font-family:Arial;font-size:xx-large;')
        );
    }
}

?>