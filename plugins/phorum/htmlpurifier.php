<?php

/**
 * HTML Purifier Phorum Mod. Filter your HTML the Standards-Compliant Way!
 * 
 * This Phorum mod enables users to post raw HTML into Phorum.  But never
 * fear: with the help of HTML Purifier, this HTML will be beat into
 * de-XSSed and standards-compliant form, safe for general consumption.
 * It is not recommended, but possible to run this mod in parallel
 * with other formatters (in short, please DISABLE the BBcode mod).
 * 
 * For help migrating from your previous markup language to pure HTML
 * please check the migrate.bbcode.php file.
 * 
 * Tested with Phorum 5.1.22. This module will almost definitely need
 * to be upgraded when Phorum 6 rolls around.
 */

if(!defined('PHORUM')) exit;

/**
 * Purifies a data array
 */
function phorum_htmlpurifier_format($data)
{
    $PHORUM = $GLOBALS["PHORUM"];
    
    $purifier =& HTMLPurifier::getInstance();
    $cache_serial = $PHORUM['mod_htmlpurifier']['body_cache_serial'];
    
    foreach($data as $message_id => $message){
        if(isset($message['body'])) {
            if (isset($message['meta']['htmlpurifier_light'])) {
                // format hook was called outside of Phorum's normal
                // functions, do the abridged purification
                $data[$message_id]['body'] = $purifier->purify($message['body']);
                continue;
            }
            
            if (
                isset($message['meta']['body_cache']) &&
                isset($message['meta']['body_cache_serial']) &&
                $message['meta']['body_cache_serial'] == $cache_serial
            ) {
                // cached version is present, bail out early
                $data[$message_id]['body'] = $message['meta']['body_cache'];
                continue;
            }
            
            // migration might edit this array, that's why it's defined
            // so early
            $updated_message = array();
            
            // create the $body variable
            if (
                !isset($message['meta']['body_cache_serial'])
            ) {
                // perform migration
                $fake_data = array();
                $fake_data[$message_id] = $message;
                $fake_data = phorum_htmlpurifier_migrate($fake_data);
                $body = $fake_data[$message_id]['body'];
                $body = str_replace("<phorum break>", "\n", $body); // dupe, but this needs to be applied early
                $updated_message['body'] = $body; // save it in
            } else {
                // reverse Phorum's pre-processing
                $body = $message['body'];
                // order is important
                $body = str_replace(array('&lt;','&gt;','&amp;'), array('<','>','&'), $body);
                $body = str_replace("<phorum break>\n", "\n", $body);
            }
            
            $body = $purifier->purify($body);
            
            // dynamically update the cache
            // this is inefficient on the first read, but the cache
            // catches will more than make up for it
            
            // this should ONLY be called on read, for posting and preview
            // phorum_htmlpurifier_posting should do the trick
            $updated_message['meta'] = $message['meta'];
            $updated_message['meta']['body_cache'] = $body;
            $updated_message['meta']['body_cache_serial'] = $cache_serial;
            phorum_db_update_message($message_id, $updated_message);
            
            // must not get overloaded until after we cache it
            $data[$message_id]['body'] = $body;
            
        }
    }
    
    return $data;
}

/**
 * Generate necessary cache and serial entries when a posting action happens
 */
function phorum_htmlpurifier_posting($message) {
    $PHORUM = $GLOBALS["PHORUM"];
    $fake_data = array($message);
    // this is a temporary attribute
    $fake_data[0]['meta']['htmlpurifier_light'] = true; // only purify, please
    list($changed_message) = phorum_hook('format', $fake_data);
    $message['meta']['body_cache'] = $changed_message['body'];
    $message['meta']['body_cache_serial'] = $PHORUM['mod_htmlpurifier']['body_cache_serial'];
    return $message;
}

/**
 * Overload quoting mechanism to prevent default, mail-style quote from happening
 */
function phorum_htmlpurifier_quote($array) {
    $PHORUM = $GLOBALS["PHORUM"];
    $purifier =& HTMLPurifier::getInstance();
    $text = $purifier->purify($array[1]);
    return "<blockquote cite=\"$array[0]\">\n$text\n</blockquote>";
}

/**
 * Ensure that our format hook is processed last. Also, loads the library.
 * @credits <http://secretsauce.phorum.org/snippets/make_bbcode_last_formatter.php.txt>
 */
function phorum_htmlpurifier_common() {
    
    require_once (dirname(__FILE__).'/htmlpurifier/HTMLPurifier.auto.php');
    
    $config_exists = file_exists(dirname(__FILE__) . '/config.php');
    if ($config_exists || !isset($PHORUM['mod_htmlpurifier']['config'])) {
        $config = HTMLPurifier_Config::createDefault();
        include(dirname(__FILE__) . '/config.default.php');
        if ($config_exists) {
            include(dirname(__FILE__) . '/config.php');
        }
    } else {
        // used cached version that was constructed from web interface
        $config = HTMLPurifier_Config::create($PHORUM['mod_htmlpurifier']['config']);
    }
    HTMLPurifier::getInstance($config);

    // increment revision.txt if you want to invalidate the cache
    $GLOBALS['PHORUM']['mod_htmlpurifier']['body_cache_serial'] = $config->getSerial();

    // load migration
    if (file_exists(dirname(__FILE__) . '/migrate.php')) {
        include(dirname(__FILE__) . '/migrate.php');
    } else {
        echo '<strong>Error:</strong> No migration path specified for HTML Purifier, please check
        <tt>modes/htmlpurifier/migrate.bbcode.php</tt> for instructions on
        how to migrate from your previous markup language.';
        exit;
    }
    
    // see if our hooks need to be bubbled to the end
    phorum_htmlpurifier_bubble_hook('format');
    
}

function phorum_htmlpurifier_bubble_hook($hook) {
    global $PHORUM;
    $our_idx = null;
    $last_idx = null;
    if (!isset($PHORUM['hooks'][$hook]['mods'])) return;
    foreach ($PHORUM['hooks'][$hook]['mods'] as $idx => $mod) {
        if ($mod == 'htmlpurifier') $our_idx = $idx;
        $last_idx = $idx;
    }
    list($mod) = array_splice($PHORUM['hooks'][$hook]['mods'], $our_idx, 1);
    $PHORUM['hooks'][$hook]['mods'][] = $mod;
    list($func) = array_splice($PHORUM['hooks'][$hook]['funcs'], $our_idx, 1);
    $PHORUM['hooks'][$hook]['funcs'][] = $func;
}

