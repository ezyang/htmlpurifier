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
 * If you'd like to use this with a WYSIWYG editor, make sure that
 * editor sets $PHORUM['mod_htmlpurifier']['wysiwyg'] to true. Otherwise,
 * administrators who need to edit other people's comments may be at
 * risk for some nasty attacks.
 * 
 * Tested with Phorum 5.1.22. This module will almost definitely need
 * to be upgraded when Phorum 6 rolls around.
 */

// Note: Cache data is base64 encoded because Phorum insists on flinging
// to the user and expecting it to come back unharmed, newlines and
// all, which ain't happening. It's slower, it takes up more space, but
// at least it won't get mutilated

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
            
            if ($message_id) {
                // we're dealing with a real message, not a fake, so
                // there a number of shortcuts that can be taken
                
                if (isset($message['meta']['htmlpurifier_light'])) {
                    // format hook was called outside of Phorum's normal
                    // functions, do the abridged purification
                    $data[$message_id]['body'] = $purifier->purify($message['body']);
                    continue;
                }
                
                if (!empty($PHORUM['args']['purge'])) {
                    // purge the cache, must be below the following if
                    unset($message['meta']['body_cache']);
                }
                
                if (
                    isset($message['meta']['body_cache']) &&
                    isset($message['meta']['body_cache_serial']) &&
                    $message['meta']['body_cache_serial'] == $cache_serial
                ) {
                    // cached version is present, bail out early
                    $data[$message_id]['body'] = base64_decode($message['meta']['body_cache']);
                    continue;
                }
            }
            
            // migration might edit this array, that's why it's defined
            // so early
            $updated_message = array();
            
            // create the $body variable
            if (
                $message_id && // message must be real to migrate
                !isset($message['meta']['body_cache_serial'])
            ) {
                // perform migration
                $fake_data = array();
                list($signature, $edit_message) = phorum_htmlpurifier_remove_sig_and_editmessage($message);
                $fake_data[$message_id] = $message;
                $fake_data = phorum_htmlpurifier_migrate($fake_data);
                $body = $fake_data[$message_id]['body'];
                $body = str_replace("<phorum break>", '', $body);
                $updated_message['body'] = $body; // save it in
                $body .= $signature . $edit_message; // add it back in
            } else {
                // reverse Phorum's pre-processing
                $body = $message['body'];
                // order is important
                $body = str_replace("<phorum break>\n", "\n", $body);
                $body = str_replace(array('&lt;','&gt;','&amp;'), array('<','>','&'), $body);
                if (!$message_id && defined('PHORUM_CONTROL_CENTER')) {
                    // we're in control.php, so it was double-escaped
                    $body = str_replace(array('&lt;','&gt;','&amp;', '&quot;'), array('<','>','&','"'), $body);
                }
            }
            
            $body = $purifier->purify($body);
            
            // dynamically update the cache (MUST BE DONE HERE!)
            // this is inefficient because it's one db call per
            // cache miss, but once the cache is in place things are
            // a lot zippier.
            
            if ($message_id) { // make sure it's not a fake id
                $updated_message['meta'] = $message['meta'];
                $updated_message['meta']['body_cache'] = base64_encode($body);
                $updated_message['meta']['body_cache_serial'] = $cache_serial;
                phorum_db_update_message($message_id, $updated_message);
            }
            
            // must not get overloaded until after we cache it, otherwise
            // we'll inadvertently change the original text
            $data[$message_id]['body'] = $body;
            
        }
    }
    
    return $data;
}

// -----------------------------------------------------------------------
// This is fragile code, copied from read.php:359. It will break if
// that is changed

/**
 * Generates a signature based on a message array
 */
function phorum_htmlpurifier_generate_sig($row) {
    $phorum_sig = '';
    if(isset($row["user"]["signature"])
       && isset($row['meta']['show_signature']) && $row['meta']['show_signature']==1){
           $phorum_sig=trim($row["user"]["signature"]);
           if(!empty($phorum_sig)){
               $phorum_sig="\n\n$phorum_sig";
           }
    }
    return $phorum_sig;
}

/**
 * Generates an edit message based on a message array
 */
function phorum_htmlpurifier_generate_editmessage($row) {
    $PHORUM = $GLOBALS['PHORUM'];
    $editmessage = '';
    if(isset($row['meta']['edit_count']) && $row['meta']['edit_count'] > 0) {
        $editmessage = str_replace ("%count%", $row['meta']['edit_count'], $PHORUM["DATA"]["LANG"]["EditedMessage"]);
        $editmessage = str_replace ("%lastedit%", phorum_date($PHORUM["short_date"],$row['meta']['edit_date']),  $editmessage);
        $editmessage = str_replace ("%lastuser%", $row['meta']['edit_username'],  $editmessage);
        $editmessage="\n\n\n\n$editmessage";
    }
    return $editmessage;
}

// End fragile code
// -----------------------------------------------------------------------

/**
 * Removes the signature and edit message from a message
 * @param $row Message passed by reference
 */
function phorum_htmlpurifier_remove_sig_and_editmessage(&$row) {
    // attempt to remove the Phorum's pre-processing:
    // we must not process the signature or editmessage
    $signature = phorum_htmlpurifier_generate_sig($row);
    $editmessage = phorum_htmlpurifier_generate_editmessage($row);
    $row['body'] = strtr($row['body'], array($signature => '', $editmessage => ''));
    return array($signature, $editmessage);
}

/**
 * Indicate that data is fully HTML and not from migration, invalidate
 * previous caches
 * @note This function used to generate the actual cache entries, but
 * since there's data missing that must be deferred to the first read
 */
function phorum_htmlpurifier_posting($message) {
    $PHORUM = $GLOBALS["PHORUM"];
    unset($message['meta']['body_cache']); // invalidate the cache
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
    
    require_once(dirname(__FILE__).'/htmlpurifier/HTMLPurifier.auto.php');
    require(dirname(__FILE__).'/init-config.php');
    
    $config = phorum_htmlpurifier_get_config();
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

/**
 * Pre-emptively performs purification if it looks like a WYSIWYG editor
 * is being used
 */
function phorum_htmlpurifier_before_editor($message) {
    if (!empty($GLOBALS['PHORUM']['mod_htmlpurifier']['wysiwyg'])) {
        if (!empty($message['body'])) {
            $body = $message['body'];
            // de-entity-ize contents
            $body = str_replace(array('&lt;','&gt;','&amp;'), array('<','>','&'), $body);
            $purifier =& HTMLPurifier::getInstance();
            $body = $purifier->purify($message['body']);
            // re-entity-ize contents
            $body = htmlspecialchars($body, ENT_QUOTES, $GLOBALS['PHORUM']['DATA']['CHARSET']);
        }
    }
    return $message;
}

function phorum_htmlpurifier_editor_after_subject() {
    // don't show this message if it's a WYSIWYG editor, since it will
    // then be handled automatically
    if (!empty($GLOBALS['PHORUM']['mod_htmlpurifier']['wysiwyg'])) return;
    ?><tr><td colspan="2" style="padding:1em 0.3em;">
  HTML input is <strong>on</strong>. Make sure you escape all HTML and
  angled-brackets with &amp;lt; and &amp;gt; (you can also use CDATA
  tags, simply wrap the suspect text with
&lt;![CDATA[<em>text</em>]]&gt;. Paragraphs will only be applied to 
double-spaces; single-spaces will not generate <tt>&lt;br&gt;</tt> tags.
    </td></tr><?php
}

