<?php

/*

TODO:
 * Reread the XML spec and make sure I got everything right
 * Add support for CDATA sections
 * Have comments output with the leading and trailing --s
 * Optimize and benchmark
 * Check MF_Text behavior: shouldn't the info in there be raw (entities parsed?)

*/

require_once 'HTMLPurifier/Lexer.php';

class HTMLPurifier_Lexer_DirectLex extends HTMLPurifier_Lexer
{
    
    // does this version of PHP support utf8 as entity function charset?
    var $_entity_utf8;
    
    function HTMLPurifier_Lexer() {
        $this->_entity_utf8 = version_compare(PHP_VERSION, '5', '>=');
    }
    
    // this is QUITE a knotty problem
    // 
    // The main trouble is that, even while assuming UTF-8 is what we're
    // using, we've got to deal with HTML entities (like &mdash;)
    // Not even sure if the PHP 5 decoding function does that. Plus,
    // SimpleTest doesn't use UTF-8!
    // 
    // However, we MUST parse everything possible, because once you get
    // to the HTML generator, it will escape everything possible (although
    // that may not be correct, and we should be using htmlspecialchars() ).
    // 
    // Nevertheless, strictly XML speaking, we cannot assume any character
    // entities are defined except the htmlspecialchars() ones, so leaving
    // the entities inside HERE is not acceptable. (plus, htmlspecialchars
    // might convert them anyway). So EVERYTHING must get parsed.
    // 
    // We may need to roll our own character entity lookup table. It's only
    // about 250, fortunantely, the decimal/hex ones map cleanly to UTF-8.
    function parseData($string) {
        // we may want to let the user do a different char encoding,
        // although there is NO REASON why they shouldn't be able
        // to convert it to UTF-8 before they pass it to us
        
        // no support for less than PHP 4.3
        if ($this->_entity_utf8) {
            // PHP 5+, UTF-8 is nicely supported
            return @html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        } else {
            // PHP 4, do compat stuff
            $string = html_entity_decode($string, ENT_QUOTES, 'ISO-8859-1');
            // get the numeric UTF-8 stuff
            $string = preg_replace('/&#(\d+);/me', "chr(\\1)", $string);
            $string = preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$string);
            // get the stringy UTF-8 stuff
            return $string;
        }
    }
    
    function nextQuote($string, $offset = 0) {
        $next = strcspn($string, '"\'', $offset) + $offset;
        return strlen($string) == $next ? false : $next;
    }
    
    function nextWhiteSpace($string, $offset = 0) {
        $next = strcspn($string, "\x20\x09\x0D\x0A", $offset) + $offset;
        return strlen($string) == $next ? false : $next;
    }
    
    function tokenizeHTML($string) {
        
        // some quick checking (if empty, return empty)
        $string = @ (string) $string;
        if ($string == '') return array();
        
        $cursor = 0; // our location in the text
        $inside_tag = false; // whether or not we're parsing the inside of a tag
        $array = array(); // result array
        
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
                        html_entity_decode(
                            substr(
                                $string, $cursor, $position_next_lt - $cursor
                            ),
                            ENT_QUOTES
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
                        html_entity_decode(
                            substr(
                                $string, $cursor
                            ),
                            ENT_QUOTES
                        )
                    );
                break;
            } elseif ($inside_tag && $position_next_gt !== false) {
                // We are in tag and it is well formed
                // Grab the internals of the tag
                $segment = substr($string, $cursor, $position_next_gt-$cursor);
                
                // Check if it's a comment
                if (
                    substr($segment,0,3) == '!--' &&
                    substr($segment,strlen($segment)-2,2) == '--'
                ) {
                    $array[] = new
                        HTMLPurifier_Token_Comment(
                            substr(
                                $segment, 3, strlen($segment) - 5
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
                $is_self_closing= (strpos($segment,'/') === strlen($segment)-1);
                if ($is_self_closing) {
                    $segment = substr($segment, 0, strlen($segment) - 1);
                }
                
                // Check if there are any attributes
                $position_first_space = $this->nextWhiteSpace($segment);
                if ($position_first_space === false) {
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
                    $attributes = $this->tokenizeAttributeString(
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
                        html_entity_decode(
                            substr($string, $cursor),
                            ENT_QUOTES
                        )
                    );
                break;
            }
            break;
        }
        return $array;
    }
    
    function tokenizeAttributeString($string) {
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
            
            $cursor += ($value = strspn($string, "\x20\x09\x0D\x0A", $cursor));
            
            $position_next_space = $this->nextWhiteSpace($string, $cursor);
            $position_next_equal = strpos($string, '=', $cursor);
            
            // grab the key
            
            $key_begin = $cursor; //we're currently at the start of the key
            
            // scroll past all characters that are the key (not whitespace or =)
            $cursor += strcspn($string, "\x20\x09\x0D\x0A=", $cursor);
            
            $key_end = $cursor; // now at the end of the key
            
            $key = substr($string, $key_begin, $key_end - $key_begin);
            
            if (!$key) continue; // empty key
            
            // scroll past all whitespace
            $cursor += strspn($string, "\x20\x09\x0D\x0A", $cursor);
            
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
                $cursor += strspn($string, "\x20\x09\x0D\x0A", $cursor);
                
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
                    $cursor += strcspn($string, "\x20\x09\x0D\x0A", $cursor);
                    $value_end = $cursor;
                }
                
                $value = substr($string, $value_begin, $value_end - $value_begin);
                $array[$key] = $value;
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