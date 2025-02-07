<?php

// mocks
class HTMLPurifier_ContextTest extends HTMLPurifier_Harness
{

    protected $context;

    public function setUp()
    {
        $this->context = new HTMLPurifier_Context();
    }

    public function testStandardUsage()
    {
        generate_mock_once('HTMLPurifier_IDAccumulator');

        $this->assertFalse($this->context->exists('IDAccumulator'));

        $accumulator = new HTMLPurifier_IDAccumulatorMock();
        $this->context->register('IDAccumulator', $accumulator);
        $this->assertTrue($this->context->exists('IDAccumulator'));

        $accumulator_2 =& $this->context->get('IDAccumulator');
        $this->assertReference($accumulator, $accumulator_2);

        $this->context->destroy('IDAccumulator');
        $this->assertFalse($this->context->exists('IDAccumulator'));

        $this->expectException(new Exception('Attempted to retrieve non-existent variable IDAccumulator'));
        $accumulator_3 =& $this->context->get('IDAccumulator');
        $this->assertNull($accumulator_3);

        $this->expectException(new Exception('Attempted to destroy non-existent variable IDAccumulator'));
        $this->context->destroy('IDAccumulator');

    }

    public function testReRegister()
    {
        $var = true;
        $this->context->register('OnceOnly', $var);

        $this->expectException(new Exception('Name OnceOnly produces collision, cannot re-register'));
        $this->context->register('OnceOnly', $var);

        // destroy it, now registration is okay
        $this->context->destroy('OnceOnly');
        $this->context->register('OnceOnly', $var);

    }

    public function test_loadArray()
    {
        // references can be *really* wonky!

        $context_manual = new HTMLPurifier_Context();
        $context_load   = new HTMLPurifier_Context();

        $var1 = 1;
        $var2 = 2;

        $context_manual->register('var1', $var1);
        $context_manual->register('var2', $var2);

        // you MUST set up the references when constructing the array,
        // otherwise the registered version will be a copy
        $array = array(
            'var1' => &$var1,
            'var2' => &$var2
        );

        $context_load->loadArray($array);
        $this->assertIdentical($context_manual, $context_load);

        $var1 = 10;
        $var2 = 20;

        $this->assertIdentical($context_manual, $context_load);

    }

    public function testNull() {
        $context = new HTMLPurifier_Context();
        $var = NULL;
        $context->register('var', $var);
        $this->assertNull($context->get('var'));
        $context->destroy('var');
    }

}

// vim: et sw=4 sts=4
