<?php

class HTMLPurifier_Array implements ArrayAccess
{
    /**
     * @param HTMLPurifier_ArrayNode
     */
    public $head = null;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var HTMLPurifier_ArrayNode
     */
    protected $offsetItem = null;


    public function __construct(array $array = array())
    {
        /**
         * @var HTMLPurifier_ArrayNode $temp
         */
        $temp = null;
        $i = 0;

        foreach ($array as &$v) {
            $item = new HTMLPurifier_ArrayNode($v);

            if ($this->head == null) {
                $this->head = &$item;
            }
            if ($temp instanceof HTMLPurifier_ArrayNode) {
                $item->prev = &$temp;
                $temp->next = &$item;
            }
            unset($temp);
            $temp = &$item;

            $i ++;

            unset($item, $v);
        }
        $this->count = $i;
        $this->offset = 0;
        $this->offsetItem = &$this->head;
    }

    protected function findIndex($offset)
    {
        if ($this->head == null) {
            return array(
                'correct' => false,
                'value' => null
            );
        }

        $current = &$this->head;
        $index = 0;

        if ($this->offset <= $offset && $this->offsetItem instanceof HTMLPurifier_ArrayNode) {
            $current = &$this->offsetItem;
            $index = $this->offset;
        }

        while ($current->next instanceof HTMLPurifier_ArrayNode && $index != $offset) {
            $current = &$current->next;
            $index ++;
        }

        if ($index == $offset) {
            $this->offset = $offset;
            $this->offsetItem = &$current;
            return array(
                'correct' => true,
                'value' => &$current
            );
        }

        return array(
            'correct' => false,
            'value' => &$current
        );
    }

    public function insertBefore($offset, $value)
    {
        $result = $this->findIndex($offset);

        $this->count ++;
        $item = new HTMLPurifier_ArrayNode($value);
        if ($result['correct'] == false) {
            if ($result['value'] instanceof HTMLPurifier_ArrayNode) {
                $result['value']->next = &$item;
                $item->prev = &$result['value'];
            }
        } else {
            if ($result['value'] instanceof HTMLPurifier_ArrayNode) {
                $item->prev = &$result['value']->prev;
                $item->next = &$result['value'];
            }
            if ($item->prev instanceof HTMLPurifier_ArrayNode) {
                $item->prev->next = &$item;
            }
            if ($result['value'] instanceof HTMLPurifier_ArrayNode) {
                $result['value']->prev = &$item;
            }
        }
        if ($offset == 0) {
            $this->head = &$item;
        }
        if ($offset <= $this->offset && $this->offsetItem instanceof HTMLPurifier_ArrayNode) {
            $this->offsetItem = &$this->offsetItem->prev;
        }
    }

    public function remove($offset)
    {
        $result = $this->findIndex($offset);

        if ($result['correct']) {
            $this->count --;
            $item = $result['value'];
            $item->prev->next = &$result['value']->next;
            $item->next->prev = &$result['value']->prev;
            if ($offset == 0) {
                $this->head = &$item->next;
            }
            if ($offset < $this->offset) {
                $this->offset --;
            } elseif ($offset == $this->offset) {
                $this->offsetItem = &$item->next;
            }
        }
    }

    public function getArray()
    {
        $return = array();
        $head = $this->head;

        while ($head instanceof HTMLPurifier_ArrayNode) {
            $return[] = $head->value;
            $head = &$head->next;
        }

        return $return;
    }

    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < $this->count;
    }

    public function offsetGet($offset)
    {
        $result = $this->findIndex($offset);
        if ($result['correct']) {
            return $result['value']->value;
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $result = $this->findIndex($offset);
        if ($result['correct']) {
            $result['value']->value = &$value;
        }
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }
}
