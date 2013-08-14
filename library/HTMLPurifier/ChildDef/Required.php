<?php

/**
 * Definition that allows a set of elements, but disallows empty children.
 */
class HTMLPurifier_ChildDef_Required extends HTMLPurifier_ChildDef
{
    /**
     * Lookup table of allowed elements.
     * @type array
     */
    public $elements = array();

    /**
     * Whether or not the last passed node was all whitespace.
     * @type bool
     */
    protected $whitespace = false;

    /**
     * @param array|string $elements List of allowed element names (lowercase).
     */
    public function __construct($elements)
    {
        if (is_string($elements)) {
            $elements = str_replace(' ', '', $elements);
            $elements = explode('|', $elements);
        }
        $keys = array_keys($elements);
        if ($keys == array_keys($keys)) {
            $elements = array_flip($elements);
            foreach ($elements as $i => $x) {
                $elements[$i] = true;
                if (empty($i)) {
                    unset($elements[$i]);
                } // remove blank
            }
        }
        $this->elements = $elements;
    }

    /**
     * @type bool
     */
    public $allow_empty = false;

    /**
     * @type string
     */
    public $type = 'required';

    /**
     * @param array $tokens_of_children
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return array
     */
    public function validateChildren($tokens_of_children, $config, $context)
    {
        // Flag for subclasses
        $this->whitespace = false;

        // if there are no tokens, delete parent node
        if (empty($tokens_of_children)) {
            return false;
        }

        // the new set of children
        $result = array();

        // current depth into the nest
        $nesting = 0;

        // whether or not we're deleting a node
        $is_deleting = false;

        // whether or not parsed character data is allowed
        // this controls whether or not we silently drop a tag
        // or generate escaped HTML from it
        $pcdata_allowed = isset($this->elements['#PCDATA']);

        // a little sanity check to make sure it's not ALL whitespace
        $all_whitespace = true;

        // some configuration
        $escape_invalid_children = $config->get('Core.EscapeInvalidChildren');

        // generator
        $gen = new HTMLPurifier_Generator($config, $context);

        foreach ($tokens_of_children as $token) {
            if (!empty($token->is_whitespace)) {
                $result[] = $token;
                continue;
            }
            $all_whitespace = false; // phew, we're not talking about whitespace

            $is_child = ($nesting == 0);

            if ($token instanceof HTMLPurifier_Token_Start) {
                $nesting++;
            } elseif ($token instanceof HTMLPurifier_Token_End) {
                $nesting--;
            }

            if ($is_child) {
                $is_deleting = false;
                if (!isset($this->elements[$token->name])) {
                    $is_deleting = true;
                    if ($pcdata_allowed && $token instanceof HTMLPurifier_Token_Text) {
                        $result[] = $token;
                    } elseif ($pcdata_allowed && $escape_invalid_children) {
                        $result[] = new HTMLPurifier_Token_Text(
                            $gen->generateFromToken($token)
                        );
                    }
                    continue;
                }
            }
            if (!$is_deleting || ($pcdata_allowed && $token instanceof HTMLPurifier_Token_Text)) {
                $result[] = $token;
            } elseif ($pcdata_allowed && $escape_invalid_children) {
                $result[] =
                    new HTMLPurifier_Token_Text(
                        $gen->generateFromToken($token)
                    );
            } else {
                // drop silently
            }
        }
        if (empty($result)) {
            return false;
        }
        if ($all_whitespace) {
            $this->whitespace = true;
            return false;
        }
        if ($tokens_of_children == $result) {
            return true;
        }
        return $result;
    }
}

// vim: et sw=4 sts=4
