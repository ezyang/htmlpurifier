<?php

/**
 * Generic property list implementation
 */
class HTMLPurifier_PropertyList
{
    /**
     * Internal data-structure for properties
     */
    protected $data = array();

    /**
     * Parent plist
     */
    protected $parent;

    /**
     * Recursively retrieves the value for a key
     */
    public function get($name) {
        if ($this->has($name)) return $this->data[$name];
        if ($this->parent) return $this->parent->get($name);
        throw new HTMLPurifier_Exception("Key '$name' not found");
    }

    /**
     * Sets the value of a key, for this plist
     */
    public function set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * Returns true if a given key exists
     */
    public function has($name) {
        return array_key_exists($name, $this->data);
    }

    /**
     * Resets a value to the value of it's parent, usually the default. If
     * no value is specified, the entire plist is reset.
     */
    public function reset($name = null) {
        if ($name == null) $this->data = array();
        else unset($this->data[$name]);
    }

    /**
     * Returns an iterator for traversing this array, optionally filtering
     * for a certain prefix.
     */
    public function getIterator($filter = null) {
        $a = new ArrayObject($this->data);
        return new HTMLPurifier_PropertyListIterator($a->getIterator(), $filter);
    }

    /**
     * Returns the parent plist.
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Sets the parent plist.
     */
    public function setParent($plist) {
        $this->parent = $plist;
    }
}

// vim: et sw=4 sts=4
