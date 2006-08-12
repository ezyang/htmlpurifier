<?php

/**
 * Internal data-structure used in attribute validation to accumulate state.
 * 
 * All it is is a data-structure that holds objects that accumulate state, like
 * HTMLPurifier_IDAccumulator.
 */

class HTMLPurifier_AttrContext
{
    var $id_accumulator;
}

?>