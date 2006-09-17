<?php

require_once 'HTMLPurifier/EntityLookup.php';
require_once 'HTMLPurifier/Encoder.php';

/**
 * Handles referencing and derefencing character entities
 */
class HTMLPurifier_EntityParser
{
    
    /**
     * Reference to entity lookup table.
     * @protected
     */
    var $_entity_lookup;
    
    /**
     * Callback regex string for parsing entities.
     * @protected
     */                             
    var $_substituteEntitiesRegex =
'/&(?:[#]x([a-fA-F0-9]+)|[#]0*(\d+)|([A-Za-z]+));?/';
//     1. hex             2. dec      3. string
    
    
    /**
     * Decimal to parsed string conversion table for special entities.
     * @protected
     */
    var $_special_dec2str =
            array(
                    34 => '"',
                    38 => '&',
                    39 => "'",
                    60 => '<',
                    62 => '>'
            );
    
    /**
     * Stripped entity names to decimal conversion table for special entities.
     * @protected
     */
    var $_special_ent2dec =
            array(
                    'quot' => 34,
                    'amp'  => 38,
                    'lt'   => 60,
                    'gt'   => 62
            );
    
    /**
     * Substitutes non-special entities with their parsed equivalents. Since
     * running this whenever you have parsed character is t3h 5uck, we run
     * it before everything else.
     * 
     * @protected
     * @param $string String to have non-special entities parsed.
     * @returns Parsed string.
     */
    function substituteNonSpecialEntities($string) {
        // it will try to detect missing semicolons, but don't rely on it
        return preg_replace_callback(
            $this->_substituteEntitiesRegex,
            array($this, 'nonSpecialEntityCallback'),
            $string
            );
    }
    
    /**
     * Callback function for substituteNonSpecialEntities() that does the work.
     * 
     * @warning Though this is public in order to let the callback happen,
     *          calling it directly is not recommended.
     * @note Based on Feyd's function at
     *       <http://forums.devnetwork.net/viewtopic.php?p=191404#191404>,
     *       which is in public domain.
     * @note While we're going to do code point parsing anyway, a good
     *       optimization would be to refuse to translate code points that
     *       are non-SGML characters.  However, this could lead to duplication.
     * @note This function is heavily intimate with the inner workings of
     *       UTF-8 and would also be well suited in the Encoder class (or at
     *       least deferring some processing to it).  This is also very
     *       similar to the unichr function in
     *       maintenance/generate-entity-file.php (although this is superior,
     *       due to its sanity checks).
     * @param $matches  PCRE matches array, with 0 the entire match, and
     *                  either index 1, 2 or 3 set with a hex value, dec value,
     *                  or string (respectively).
     * @returns Replacement string.
     */
    
    // +----------+----------+----------+----------+
    // | 33222222 | 22221111 | 111111   |          |
    // | 10987654 | 32109876 | 54321098 | 76543210 | bit
    // +----------+----------+----------+----------+
    // |          |          |          | 0xxxxxxx | 1 byte 0x00000000..0x0000007F
    // |          |          | 110yyyyy | 10xxxxxx | 2 byte 0x00000080..0x000007FF
    // |          | 1110zzzz | 10yyyyyy | 10xxxxxx | 3 byte 0x00000800..0x0000FFFF
    // | 11110www | 10wwzzzz | 10yyyyyy | 10xxxxxx | 4 byte 0x00010000..0x0010FFFF
    // +----------+----------+----------+----------+
    // | 00000000 | 00011111 | 11111111 | 11111111 | Theoretical upper limit of legal scalars: 2097151 (0x001FFFFF)
    // | 00000000 | 00010000 | 11111111 | 11111111 | Defined upper limit of legal scalar codes
    // +----------+----------+----------+----------+ 
    
    function nonSpecialEntityCallback($matches) {
        // replaces all but big five
        $entity = $matches[0];
        $is_num = (@$matches[0][1] === '#');
        if ($is_num) {
            $is_hex = (@$entity[2] === 'x');
            $code = $is_hex ? hexdec($matches[1]) : (int) $matches[2];
            
            // abort for special characters
            if (isset($this->_special_dec2str[$code]))  return $entity;
            
            return HTMLPurifier_Encoder::unichr($code);
        } else {
            if (isset($this->_special_ent2dec[$matches[3]])) return $entity;
            if (!$this->_entity_lookup) {
                require_once 'HTMLPurifier/EntityLookup.php';
                $this->_entity_lookup = HTMLPurifier_EntityLookup::instance();
            }
            if (isset($this->_entity_lookup->table[$matches[3]])) {
                return $this->_entity_lookup->table[$matches[3]];
            } else {
                return $entity;
            }
        }
    }
    
    /**
     * Substitutes only special entities with their parsed equivalents.
     * 
     * @notice We try to avoid calling this function because otherwise, it
     * would have to be called a lot (for every parsed section).
     * 
     * @protected
     * @param $string String to have non-special entities parsed.
     * @returns Parsed string.
     */
    function substituteSpecialEntities($string) {
        return preg_replace_callback(
            $this->_substituteEntitiesRegex,
            array($this, 'specialEntityCallback'),
            $string);
    }
    
    /**
     * Callback function for substituteSpecialEntities() that does the work.
     * 
     * This callback has same syntax as nonSpecialEntityCallback().
     * 
     * @warning Though this is public in order to let the callback happen,
     *          calling it directly is not recommended.
     * @param $matches  PCRE-style matches array, with 0 the entire match, and
     *                  either index 1, 2 or 3 set with a hex value, dec value,
     *                  or string (respectively).
     * @returns Replacement string.
     */
    function specialEntityCallback($matches) {
        $entity = $matches[0];
        $is_num = (@$matches[0][1] === '#');
        if ($is_num) {
            $is_hex = (@$entity[2] === 'x');
            $int = $is_hex ? hexdec($matches[1]) : (int) $matches[2];
            return isset($this->_special_dec2str[$int]) ?
                $this->_special_dec2str[$int] :
                $entity;
        } else {
            return isset($this->_special_ent2dec[$matches[3]]) ?
                $this->_special_ent2dec[$matches[3]] :
                $entity;
        }
    }
    
}

?>