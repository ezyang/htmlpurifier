<?php

class HTMLPurifier_PropertyListTest extends UnitTestCase
{
    
    function testBasic() {
        $plist = new HTMLPurifier_PropertyList();
        $plist->set('key', 'value');
        $this->assertIdentical($plist->get('key'), 'value');
    }
    
    function testNotFound() {
        $this->expectException(new HTMLPurifier_Exception("Key 'key' not found"));
        $plist = new HTMLPurifier_PropertyList();
        $plist->get('key');
    }
    
    function testRecursion() {
        $parent_plist = new HTMLPurifier_PropertyList();
        $parent_plist->set('key', 'value');
        $plist = new HTMLPurifier_PropertyList();
        $plist->setParent($parent_plist);
        $this->assertIdentical($plist->get('key'), 'value');
    }
    
    function testOverride() {
        $parent_plist = new HTMLPurifier_PropertyList();
        $parent_plist->set('key', 'value');
        $plist = new HTMLPurifier_PropertyList();
        $plist->setParent($parent_plist);
        $plist->set('key',  'value2');
        $this->assertIdentical($plist->get('key'), 'value2');
    }
    
    function testRecursionNotFound() {
        $this->expectException(new HTMLPurifier_Exception("Key 'key' not found"));
        $parent_plist = new HTMLPurifier_PropertyList();
        $plist = new HTMLPurifier_PropertyList();
        $plist->setParent($parent_plist);
        $this->assertIdentical($plist->get('key'), 'value');
    }
    
    function testHas() {
        $plist = new HTMLPurifier_PropertyList();
        $this->assertIdentical($plist->has('key'), false);
        $plist->set('key', 'value');
        $this->assertIdentical($plist->has('key'), true);
    }
    
    function testReset() {
        $plist = new HTMLPurifier_PropertyList();
        $plist->set('key1', 'value');
        $plist->set('key2', 'value');
        $plist->set('key3', 'value');
        $this->assertIdentical($plist->has('key1'), true);
        $this->assertIdentical($plist->has('key2'), true);
        $this->assertIdentical($plist->has('key3'), true);
        $plist->reset('key2');
        $this->assertIdentical($plist->has('key1'), true);
        $this->assertIdentical($plist->has('key2'), false);
        $this->assertIdentical($plist->has('key3'), true);
        $plist->reset();
        $this->assertIdentical($plist->has('key1'), false);
        $this->assertIdentical($plist->has('key2'), false);
        $this->assertIdentical($plist->has('key3'), false);
    }
    
    function testIterator() {
        $plist = new HTMLPurifier_PropertyList();
        $plist->set('nkey1', 'v');
        $plist->set('nkey2', 'v');
        $plist->set('rkey3', 'v');
        $a = array();
        foreach ($plist->getIterator() as $key => $value) {
            $a[$key] = $value;
        }
        $this->assertIdentical($a, array('nkey1' => 'v', 'nkey2' => 'v', 'rkey3' => 'v'));
        $a = array();
        foreach ($plist->getIterator('nkey') as $key => $value) {
            $a[$key] = $value;
        }
        $this->assertIdentical($a, array('nkey1' => 'v', 'nkey2' => 'v'));
    }
    
}
