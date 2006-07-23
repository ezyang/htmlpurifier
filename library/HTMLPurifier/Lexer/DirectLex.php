<?php

require_once 'HTMLPurifier/Lexer.php';

/**
 * Our in-house implementation of a parser.
 * 
 * A pure PHP parser, DirectLex has absolutely no dependencies, making
 * it a reasonably good default for PHP4.  Written with efficiency in mind,
 * it can be four times faster than HTMLPurifier_Lexer_PEARSax3, although it
 * pales in comparison to HTMLPurifier_Lexer_DOMLex.  It will support UTF-8
 * completely eventually.
 * 
 * @todo Implement non-special string entity conversion.
 * @todo Reread XML spec and document differences.
 * @todo Add support for CDATA sections.
 * @todo Determine correct behavior in outputting comment data. (preserve dashes?)
 * @todo Optimize main function tokenizeHTML().
 */
class HTMLPurifier_Lexer_DirectLex extends HTMLPurifier_Lexer
{
    
    /**
     * Parses special entities into the proper characters.
     * 
     * This string will translate escaped versions of the special characters
     * into the correct ones.
     * 
     * @warning
     * You should be able to treat the output of this function as
     * completely parsed, but that's only because all other entities should
     * have been handled previously in substituteNonSpecialEntities()
     * 
     * @param $string String character data to be parsed.
     * @returns Parsed character data.
     */
    function parseData($string) {
        
        // subtracts amps that cannot possibly be escaped
        $num_amp = substr_count($string, '&') - substr_count($string, '& ') -
            ($string[strlen($string)-1] === '&' ? 1 : 0);
        
        if (!$num_amp) return $string; // abort if no entities
        $num_esc_amp = substr_count($string, '&amp;');
        $string = strtr($string, $this->_special_entity2str);
        
        // code duplication for sake of optimization, see above
        $num_amp_2 = substr_count($string, '&') - substr_count($string, '& ') -
            ($string[strlen($string)-1] === '&' ? 1 : 0);
        
        if ($num_amp_2 <= $num_esc_amp) return $string;
        
        // hmm... now we have some uncommon entities. Use the callback.
        $string = $this->substituteSpecialEntities($string);
        return $string;
    }
    
    /**
     * Whitespace characters for str(c)spn.
     * @protected
     */
    var $_whitespace = "\x20\x09\x0D\x0A";
    
    /**
     * Decimal to parsed string conversion table for special entities.
     * @protected
     */
    var $_special_dec2str = array(
            34 => '"', // quote
            38 => '&', // ampersand            
            39 => "'", // apostrophe           
            60 => '<', // less than sign
            62 => '>'  // greater than sign
        );
    
    /**
     * Stripped entity names to decimal conversion table for special entities.
     * @protected
     */
    var $_special_ent2dec = array(
            'quot' => 34,
            'amp'  => 38,
            'lt'   => 60,
            'gt'   => 62,
        );
    
    /**
     * Most common entity to raw value conversion table for special entities.
     * @protected
     */
    var $_special_entity2str = array(
            '&quot;' => '"',
            '&amp;'  => '&',
            '&lt;'   => '<',
            '&gt;'   => '>',
            '&#39;'  => "'",
            '&#039;' => "'",
            '&#x27;' => "'",
        );
    
    /**
     * Callback regex string for parsing entities.
     * @protected
     */
    var $_substituteEntitiesRegex =
        //       1. hex          2. dec  3. string
        '/&[#](?:x([a-fA-F0-9]+)|0*(\d+)|([A-Za-z]+));?/';
    
    /**
     * Substitutes non-special entities with their parsed equivalents.
     */
    function substituteNonSpecialEntities($string) {
        // it will try to detect missing semicolons, but don't rely on it
        return preg_replace_callback(
            $this->_substituteEntitiesRegex,
            array('HTMLPurifier_Lexer_DirectLex', 'nonSpecialEntityCallback'),
            $string);
    }
    
    /**
     * Callback function for substituteNonSpecialEntities() that does the work.
     */
    function nonSpecialEntityCallback($matches) {
        // replaces all but big five
        $entity = $matches[0];
        $is_num = (@$matches[0][1] === '#');
        if ($is_num) {
            $is_hex = (@$entity[2] === 'x');
            $int = $is_hex ? hexdec($matches[1]) : (int) $matches[2];
            if (isset($this->_special_dec2str[$int]))  return $entity;
            return chr($int);
        } else {
            if (isset($this->_special_ent2dec[$matches[3]])) return $entity;
            // translate $matches[3]
        }
    }
    
    /**
     * Substitutes only special entities with their parsed equivalents.
     * 
     * We try to avoid calling this function because otherwise, it would have
     * to be called a lot (for every parsed section).
     */
    function substituteSpecialEntities($string) {
        return preg_replace_callback(
            $this->_substituteEntitiesRegex,
            array('HTMLPurifier_Lexer_DirectLex', 'specialEntityCallback'),
            $string);
    }
    
    /**
     * Callback function for substituteSpecialEntities() that does the work.
     * 
     * This callback is very similar to nonSpecialEntityCallback().
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
    
    function tokenizeHTML($string) {
        
        // some quick checking (if empty, return empty)
        $string = @ (string) $string;
        if ($string == '') return array();
        
        $cursor = 0; // our location in the text
        $inside_tag = false; // whether or not we're parsing the inside of a tag
        $array = array(); // result array
        
        // expand entities THAT AREN'T THE BIG FIVE
        $string = $this->substituteNonSpecialEntities($string);
        
        // infinite loop protection
        // has to be pretty big, since html docs can be big
        // we're allow two hundred thousand tags... more than enough?
        $loops = 0;
        
        while(true) {
            
            // infinite loop protection
            if (++$loops > 200000) return array();
            
            $position_next_lt = strpos($string, '<', $cursor);
            $position_next_gt = strpos($string, '>', $cursor);
            
            // triggers on "<b>asdf</b>" but not "asdf <b></b>"
            if ($position_next_lt === $cursor) {
                $inside_tag = true;
                $cursor++;
            }
            
            if (!$inside_tag && $position_next_lt !== false) {
                // We are not inside tag and there still is another tag to parse
                $array[] = new
                    HTMLPurifier_Token_Text(
                        $this->parseData(
                            substr(
                                $string, $cursor, $position_next_lt - $cursor
                            )
                        )
                    );
                $cursor  = $position_next_lt + 1;
                $inside_tag = true;
                continue;
            } elseif (!$inside_tag) {
                // We are not inside tag but there are no more tags
                // If we're already at the end, break
                if ($cursor === strlen($string)) break;
                // Create Text of rest of string
                $array[] = new
                    HTMLPurifier_Token_Text(
                        $this->parseData(
                            substr(
                                $string, $cursor
                            )
                        )
                    );
                break;
            } elseif ($inside_tag && $position_next_gt !== false) {
                // We are in tag and it is well formed
                // Grab the internals of the tag
                $strlen_segment = $position_next_gt - $cursor;
                $segment = substr($string, $cursor, $strlen_segment);
                
                // Check if it's a comment
                if (
                    substr($segment, 0, 3) == '!--' &&
                    substr($segment, $strlen_segment-2, 2) == '--'
                ) {
                    $array[] = new
                        HTMLPurifier_Token_Comment(
                            substr(
                                $segment, 3, $strlen_segment - 5
                            )
                        );
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }
                
                // Check if it's an end tag
                $is_end_tag = (strpos($segment,'/') === 0);
                if ($is_end_tag) {
                    $type = substr($segment, 1);
                    $array[] = new HTMLPurifier_Token_End($type);
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }
                
                // Check if it is explicitly self closing, if so, remove
                // trailing slash. Remember, we could have a tag like <br>, so
                // any later token processing scripts must convert improperly
                // classified EmptyTags from StartTags.
                $is_self_closing= (strpos($segment,'/') === $strlen_segment-1);
                if ($is_self_closing) {
                    $strlen_segment--;
                    $segment = substr($segment, 0, $strlen_segment);
                }
                
                // Check if there are any attributes
                $position_first_space = strcspn($segment, $this->_whitespace);
                
                if ($position_first_space >= $strlen_segment) {
                    if ($is_self_closing) {
                        $array[] = new HTMLPurifier_Token_Empty($segment);
                    } else {
                        $array[] = new HTMLPurifier_Token_Start($segment);
                    }
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }
                
                // Grab out all the data
                $type = substr($segment, 0, $position_first_space);
                $attribute_string =
                    trim(
                        substr(
                            $segment, $position_first_space
                        )
                    );
                if ($attribute_string) {
                    $attributes = $this->parseAttributeString(
                                        $attribute_string
                                  );
                } else {
                    $attributes = array();
                }
                
                if ($is_self_closing) {
                    $array[] = new HTMLPurifier_Token_Empty($type, $attributes);
                } else {
                    $array[] = new HTMLPurifier_Token_Start($type, $attributes);
                }
                $cursor = $position_next_gt + 1;
                $inside_tag = false;
                continue;
            } else {
                $array[] = new
                    HTMLPurifier_Token_Text(
                        '<' .
                        $this->parseData(
                            substr($string, $cursor)
                        )
                    );
                break;
            }
            break;
        }
        return $array;
    }
    
    /**
     * Takes the inside of an HTML tag and makes an assoc array of attributes.
     * 
     * @param $string Inside of tag excluding name.
     * @return Assoc array of attributes.
     */
    function parseAttributeString($string) {
        $string = (string) $string; // quick typecast
        
        if ($string == '') return array(); // no attributes
        
        // let's see if we can abort as quickly as possible
        // one equal sign, no spaces => one attribute
        $num_equal = substr_count($string, '=');
        $has_space = strpos($string, ' ');
        if ($num_equal === 0 && !$has_space) {
            // bool attribute
            return array($string => $string);
        } elseif ($num_equal === 1 && !$has_space) {
            // only one attribute
            list($key, $quoted_value) = explode('=', $string);
            $quoted_value = trim($quoted_value);
            if (!$key) return array();
            if (!$quoted_value) return array($key => '');
            $first_char = @$quoted_value[0];
            $last_char  = @$quoted_value[strlen($quoted_value)-1];
            
            $same_quote = ($first_char == $last_char);
            $open_quote = ($first_char == '"' || $first_char == "'");
            
            if ( $same_quote && $open_quote) {
                // well behaved
                $value = substr($quoted_value, 1, strlen($quoted_value) - 2);
            } else {
                // not well behaved
                if ($open_quote) {
                    $value = substr($quoted_value, 1);
                } else {
                    $value = $quoted_value;
                }
            }
            return array($key => $value);
        }
        
        // setup loop environment
        $array  = array(); // return assoc array of attributes
        $cursor = 0; // current position in string (moves forward)
        $size   = strlen($string); // size of the string (stays the same)
        
        // if we have unquoted attributes, the parser expects a terminating
        // space, so let's guarantee that there's always a terminating space.
        $string .= ' ';
        
        // infinite loop protection
        $loops = 0;
        
        while(true) {
            
            // infinite loop protection
            if (++$loops > 1000) return array();
            
            if ($cursor >= $size) {
                break;
            }
            
            $cursor += ($value = strspn($string, $this->_whitespace, $cursor));
            
            // grab the key
            
            $key_begin = $cursor; //we're currently at the start of the key
            
            // scroll past all characters that are the key (not whitespace or =)
            $cursor += strcspn($string, $this->_whitespace . '=', $cursor);
            
            $key_end = $cursor; // now at the end of the key
            
            $key = substr($string, $key_begin, $key_end - $key_begin);
            
            if (!$key) continue; // empty key
            
            // scroll past all whitespace
            $cursor += strspn($string, $this->_whitespace, $cursor);
            
            if ($cursor >= $size) {
                $array[$key] = $key;
                break;
            }
            
            // if the next character is an equal sign, we've got a regular
            // pair, otherwise, it's a bool attribute
            $first_char = @$string[$cursor];
            
            if ($first_char == '=') {
                // key="value"
                
                $cursor++;
                $cursor += strspn($string, $this->_whitespace, $cursor);
                
                // we might be in front of a quote right now
                
                $char = @$string[$cursor];
                
                if ($char == '"' || $char == "'") {
                    // it's quoted, end bound is $char
                    $cursor++;
                    $value_begin = $cursor;
                    $cursor = strpos($string, $char, $cursor);
                    $value_end = $cursor;
                } else {
                    // it's not quoted, end bound is whitespace
                    $value_begin = $cursor;
                    $cursor += strcspn($string, $this->_whitespace, $cursor);
                    $value_end = $cursor;
                }
                
                $value = substr($string, $value_begin, $value_end - $value_begin);
                $array[$key] = $this->parseData($value);
                $cursor++;
                
            } else {
                // boolattr
                if ($key !== '') {
                    $array[$key] = $key;
                }
                
            }
        }
        return $array;
    }
    
}

?>