<?php

/**
 * Internal data-structure used in attribute validation to accumulate state.
 * 
 * This is a data-structure that holds objects that accumulate state, like
 * HTMLPurifier_IDAccumulator. It's better than using globals!
 * 
 * @note Many functions that accept this object have it as a mandatory
 *       parameter, even when there is no use for it.  Though this is
 *       for the same reasons as why HTMLPurifier_Config is a mandatory
 *       parameter, it is also because you cannot assign a default value
 *       to a parameter passed by reference (passing by reference is essential
 *       for context to work in PHP 4).
 */

class HTMLPurifier_AttrContext
{
    /**
     * Contains an HTMLPurifier_IDAccumulator, which keeps track of used IDs.
     * @public
     */
    var $id_accumulator;
}

?>