<?php

/*
Lexes SGML style documents, aka HTML, XML, XHMTML, you name it.

TODO:
 * Validate element names and attributes for correct composition
 * Reread the XML spec and make sure I got everything right

*/

class MarkupLexer
{
    
    function nextQuote($string, $offset = 0) {
        $quotes = array('"', "'");
        return $this->next($string, $quotes, $offset);
    }
    
    function nextWhiteSpace($string, $offset = 0) {
        $spaces = array(chr(0x20), chr(0x9), chr(0xD), chr(0xA));
        return $this->next($string, $spaces, $offset);
    }
    
    function next($haystack, $needles, $offset = 0) {
        if (is_string($needles)) {
            $string_needles = $needles;
            $needles = array();
            $size = strlen($string_needles);
            for ($i = 0; $i < $size; $i++) {
                $needles[] = $string_needles{$i};
            }
        }
        $positions = array();
        foreach ($needles as $needle) {
            $position = strpos($haystack, $needle, $offset);
            if ($position !== false) {
                $positions[] = $position;
            }
        }
        return empty($positions) ? false : min($positions);
    }
    
    function tokenizeHTML($string) {
        
        // some quick checking (if empty, return empty)
        $string = (string) $string;
        if ($string == '') return array();
        
        $cursor = 0; // our location in the text
        $inside_tag = false; // whether or not we're parsing the inside of a tag
        $array = array(); // result array
        
        while(true) {
            
            $position_next_lt = strpos($string, '<', $cursor);
            $position_next_gt = strpos($string, '>', $cursor);
            
            // triggers on "<b>asdf</b>" but not "asdf <b></b>"
            if ($position_next_lt === $cursor) {
                $inside_tag = true;
                $cursor++;
            }
            
            if (!$inside_tag && $position_next_lt !== false) {
                // We are not inside tag and there still is another tag to parse
                $array[] = new MF_Text(substr($string, $cursor, $position_next_lt - $cursor));
                $cursor  = $position_next_lt + 1;
                $inside_tag = true;
                continue;
            } elseif (!$inside_tag) {
                // We are not inside tag but there are no more tags
                // If we're already at the end, break
                if ($cursor === strlen($string)) break;
                // Create Text of rest of string
                $array[] = new MF_Text(substr($string, $cursor));
                break;
            } elseif ($inside_tag && $position_next_gt !== false) {
                // We are in tag and it is well formed
                // Grab the internals of the tag
                $segment = substr($string, $cursor, $position_next_gt - $cursor);
                
                // Check if it's a comment
                if (substr($segment,0,3) == '!--' && substr($segment,strlen($segment)-2,2) == '--') {
                    $array[] = new MF_Comment(substr($segment,3,strlen($segment)-5));
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }
                
                // Check if it's an end tag
                $is_end_tag = (strpos($segment,'/') === 0);
                if ($is_end_tag) {
                    $type = substr($segment, 1);
                    $array[] = new MF_EndTag($type);
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }
                
                // Check if it is self closing, if so, remove trailing slash
                $is_self_closing = (strpos($segment,'/') === strlen($segment) - 1);
                if ($is_self_closing) {
                    $segment = substr($segment, 0, strlen($segment) - 1);
                }
                
                // Check if there are any attributes
                $position_first_space = $this->nextWhiteSpace($segment);
                if ($position_first_space === false) {
                    if ($is_self_closing) {
                        $array[] = new MF_EmptyTag($segment);
                    } else {
                        $array[] = new MF_StartTag($segment, array());
                    }
                    $inside_tag = false;
                    $cursor = $position_next_gt + 1;
                    continue;
                }
                
                // Grab out all the data
                $type = substr($segment, 0, $position_first_space);
                $attribute_string = trim(substr($segment, $position_first_space));
                $attributes = $this->tokenizeAttributeString($attribute_string);
                if ($is_self_closing) {
                    $array[] = new MF_EmptyTag($type, $attributes);
                } else {
                    $array[] = new MF_StartTag($type, $attributes);
                }
                $cursor = $position_next_gt + 1;
                $inside_tag = false;
                continue;
            } else {
                $array[] = new MF_Text('<' . substr($string, $cursor));
                break;
            }
            break;
        }
        return $array;
    }
    
    function tokenizeAttributeString($string) {
        $string = (string) $string;
        if ($string == '') return array();
        
        $array = array();
        $cursor = 0;
        $in_value = false;
        $i = 0;
        $size = strlen($string);
        while(true) {
            if ($cursor >= $size) {
                break;
            }
            $position_next_space = $this->nextWhiteSpace($string, $cursor);
            //scroll to the last whitespace before text
            while ($position_next_space === $cursor) {
                $cursor++;
                $position_next_space = $this->nextWhiteSpace($string, $cursor);
            }
            $position_next_equal = strpos($string, '=', $cursor);
            if ($position_next_equal !== false &&
                 ($position_next_equal < $position_next_space ||
                  $position_next_space === false)) {
                //attr="asdf"
                $key = trim(substr($string, $cursor, $position_next_equal - $cursor));
                $position_next_quote = $this->nextQuote($string, $cursor);
                $quote = $string{$position_next_quote};
                $position_end_quote = strpos($string, $quote, $position_next_quote + 1);
                $value = substr($string, $position_next_quote + 1,
                  $position_end_quote - $position_next_quote - 1);
                if ($key) {
                    $array[$key] = $value;
                }
                $cursor = $position_end_quote + 1;
            } else {
                //boolattr
                if ($position_next_space === false) {
                    $position_next_space = $size;
                }
                $key = substr($string, $cursor, $position_next_space - $cursor);
                if ($key) {
                    $array[$key] = $key;
                }
                $cursor = $position_next_space + 1;
            }
        }
        return $array;
    }
    
}

?>