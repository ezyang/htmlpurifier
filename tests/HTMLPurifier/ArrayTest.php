<?php

class HTMLPurifier_ArrayTest extends UnitTestCase
{
    /**
     * Data provider for the rest of tests
     * @return array
     */
    public function getData()
    {
        return array(
            array(array()),
            array(array(1, 2, 3, 4))
        );
    }

    /**
     * Testing of initialization of properties of HTMLPurifier_Array
     */
    public function testConstruct()
    {
        $array = $this->getData();
        $object = new HTMLPurifier_ArrayMock($array);

        $this->assertEqual(0, $object->getOffset());
        $this->assertEqual($object->getHead(), $object->getOffsetItem());
        $this->assertEqual(count($array), $object->getCount());
        $this->assertEqual($array, $object->getArray());
    }

    /**
     * Testing of offset & offsetItem properties while seeking/removing/inserting
     */
    public function testFindIndex()
    {
        $array = array(1, 2, 3, 4, 5);
        $object = new HTMLPurifier_ArrayMock($array);
        for ($i = 0; $i < $object->getCount(); $i ++) {
            $object[$i];
            $this->assertEqual($i, $object->getOffset());
            $this->assertEqual($array[$i], $object->getOffsetItem()->value);
        }

        $object[2];
        $this->assertEqual(2, $object->getOffset());
        $this->assertEqual(3, $object->getOffsetItem()->value);
        $object->remove(2);
        $this->assertEqual(2, $object->getOffset());
        $this->assertEqual(4, $object->getOffsetItem()->value);

        $object[1];
        $this->assertEqual(1, $object->getOffset());
        $this->assertEqual(2, $object->getOffsetItem()->value);
        $object->insertBefore(1, 'a');
        $this->assertEqual(1, $object->getOffset());
        $this->assertEqual('a', $object->getOffsetItem()->value);
    }

    /**
     * Testing that behavior of insertBefore the same as array_splice
     */
    public function testInsertBefore()
    {
        $array = $this->getData();
        $object = new HTMLPurifier_ArrayMock($array);

        $index = 0;
        array_splice($array, $index, 0, array('a'));
        $object->insertBefore($index, 'a');
        $this->assertEqual($array, $object->getArray());

        $index = 2;
        array_splice($array, $index, 0, array('a'));
        $object->insertBefore($index, 'a');
        $this->assertEqual($array, $object->getArray());

        $index = count($array) * 2;
        array_splice($array, $index, 0, array('a'));
        $object->insertBefore($index, 'a');
        $this->assertEqual($array, $object->getArray());
    }

    /**
     * Testing that behavior of remove the same as array_splice
     */
    public function testRemove()
    {
        $array = $this->getData();
        $object = new HTMLPurifier_ArrayMock($array);

        $index = 0;
        array_splice($array, $index, 1);
        $object->remove($index);
        $this->assertEqual($array, $object->getArray());

        $index = 2;
        array_splice($array, $index, 1);
        $object->remove($index);
        $this->assertEqual($array, $object->getArray());

        $index = count($array) * 2;
        array_splice($array, $index, 1);
        $object->remove($index);
        $this->assertEqual($array, $object->getArray());
    }

    /**
     * Testing that object returns original array
     */
    public function testGetArray()
    {
        $array = $this->getData();
        $object = new HTMLPurifier_ArrayMock($array);
        $this->assertEqual($array, $object->getArray());
    }

    /**
     * Testing ArrayAccess interface
     */
    public function testOffsetExists()
    {
        $array = $this->getData();
        $object = new HTMLPurifier_ArrayMock($array);
        $this->assertEqual(isset($array[0]), isset($object[0]));
    }

    /**
     * Testing ArrayAccess interface
     */
    public function testOffsetGet()
    {
        $array = array(1, 2, 3);
        $object = new HTMLPurifier_ArrayMock($array);
        foreach ($array as $k => $v) {
            $this->assertEqual($v, $object[$k]);
        }
    }

    /**
     * Testing ArrayAccess interface
     */
    public function testOffsetSet()
    {
        $array = array(1, 2, 3);
        $object = new HTMLPurifier_ArrayMock($array);
        foreach ($array as $k => $v) {
            $v = $v * 2;
            $object[$k] = $v;
            $this->assertEqual($v, $object[$k]);
        }
    }

    /**
     * Testing ArrayAccess interface
     * There is one difference: keys are updated as well, they are started from 0
     */
    public function testOffsetUnset()
    {
        $object = new HTMLPurifier_ArrayMock(array(1, 2, 3, 4));
        unset($object[1]);
        $this->assertEqual(array(1, 3, 4), $object->getArray());
        unset($object[0]);
        $this->assertEqual(array(3, 4), $object->getArray());
        unset($object[1]);
        $this->assertEqual(array(3), $object->getArray());
        unset($object[0]);
        $this->assertEqual(array(), $object->getArray());
    }
}

/**
 * Mock for some protected properties of HTMLPurifier_Array
 */
class HTMLPurifier_ArrayMock extends HTMLPurifier_Array
{
    /**
     * @return HTMLPurifier_ArrayNode|null
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return HTMLPurifier_ArrayNode|null
     */
    public function getOffsetItem()
    {
        return $this->offsetItem;
    }
}
