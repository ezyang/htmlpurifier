<?php

// based off of BBCode's settings file

/**
 * HTML Purifier Phorum mod settings configuration. This provides
 * a convenient web-interface for editing the most common HTML Purifier
 * configuration directives. You can also specify custom configuration
 * by creating a 'config.php' file.
 */

if(!defined("PHORUM_ADMIN")) exit;

// load library
require_once (dirname(__FILE__).'/htmlpurifier/HTMLPurifier.auto.php');

// define friendly configuration directives
$directives = array(
    'URI.Host', // auto-detectable
    'URI.DisableExternal',
    'URI.DisableExternalResources',
    'URI.DisableResources',
    'URI.Munge',
    'URI.HostBlacklist',
    'URI.Disable',
    'HTML.TidyLevel',
    'HTML.Doctype', // auto-detectable
    'HTML.Allowed',
    'AutoFormat',
    '-AutoFormat.Custom',
    '-AutoFormat.PurifierLinkify',
    'Output.TidyFormat',
);

if (isset($_POST['reset'])) {
    unset($PHORUM['mod_htmlpurifier']['config']);
}

// instantiate $config object
$config_exists = file_exists(dirname(__FILE__) . '/config.php');
if ($config_exists || !isset($PHORUM['mod_htmlpurifier']['config'])) {
    $config = HTMLPurifier_Config::createDefault();
    include(dirname(__FILE__) . '/config.default.php');
    if ($config_exists) {
        include(dirname(__FILE__) . '/config.php');
    }
    unset($PHORUM['mod_htmlpurifier']['config']); // unnecessary
} else {
    $config = HTMLPurifier_Config::create($PHORUM['mod_htmlpurifier']['config']);
}

$offset = 0;
if (!empty($_POST['migrate-sigs'])) {
    if (!isset($_POST['confirmation']) || strtolower($_POST['confirmation']) !== 'yes') {
        echo 'Invalid confirmation code.';
        exit;
    }
    $PHORUM['mod_htmlpurifier']['migrate-sigs'] = true;
    phorum_db_update_settings(array("mod_htmlpurifier"=>$PHORUM["mod_htmlpurifier"]));
    $offset = 1;
} elseif (!empty($_GET['migrate-sigs']) && $PHORUM['mod_htmlpurifier']['migrate-sigs']) {
    $offset = (int) $_GET['migrate-sigs'];
}

// lower this setting if you're getting time outs/out of memory
$increment = 100;

if ($offset) do {
    require_once 'migrate.php';
    // migrate signatures
    // do this in batches so we don't run out of time/space
    $end = $offset + $increment;
    $user_ids = array();
    for ($i = $offset; $i < $end; $i++) {
        $user_ids[] = $i;
    }
    $userinfos = phorum_db_user_get_fields($user_ids, 'signature');
    foreach ($userinfos as $i => $user) {
        if (empty($user['signature'])) continue;
        $sig = $user['signature'];
        // perform standard Phorum processing on the sig
        $sig = str_replace(array("&","<",">"), array("&amp;","&lt;","&gt;"), $sig);
        $sig = preg_replace("/<((http|https|ftp):\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!*'\(\),~%]+?)>/i", "$1", $sig);
        // prepare fake data to pass to migration function
        $fake_data = array(array("author"=>"", "email"=>"", "subject"=>"", 'body' => $sig));
        list($fake_message) = phorum_htmlpurifier_migrate($fake_data);
        $user['signature'] = $fake_message['body'];
        if (!phorum_user_save($user)) {
            exit('Error while saving user data');
        }
    }
    unset($userinfos); // free up memory
    
    // query for highest ID in database
    $type = $PHORUM['DBCONFIG']['type'];
    if ($type == 'mysql') {
        $conn = phorum_db_mysql_connect();
        $sql = "select MAX(user_id) from {$PHORUM['user_table']}";
        $res = mysql_query($sql, $conn);
        $row = mysql_fetch_row($res);
        $top_id = (int) $row[0];
    } elseif ($type == 'mysqli') {
        $conn = phorum_db_mysqli_connect();
        $sql = "select MAX(user_id) from {$PHORUM['user_table']}";
        $res = mysqli_query($conn, $sql);
        $row = mysqli_fetch_row($res);
        $top_id = (int) $row[0];
    } else {
        exit('Unrecognized database!');
    }
    
    $offset += $increment;
    if ($offset > $top_id) { // test for end condition
        echo 'Migration finished';
        $PHORUM['mod_htmlpurifier']['migrate-sigs'] = false;
        phorum_db_update_settings(array("mod_htmlpurifier"=>$PHORUM["mod_htmlpurifier"]));
        continue;
    }
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $extra = 'admin.php?module=modsettings&mod=htmlpurifier&migrate-sigs=' . $offset;
    // relies on output buffering to work
    header("Location: http://$host$uri/$extra");
    exit;
} while (0);

if(!empty($_POST) && !$offset){
    // save settings
    if ($config_exists) {
        echo "Cannot update settings, <code>mods/htmlpurifier/config.php</code> already exists. To change
        settings, edit that file. To use the web form, delete that file.<br />";
    } else {
        if (!isset($_POST['reset'])) $config->mergeArrayFromForm($_POST, 'config', $directives);
        $PHORUM['mod_htmlpurifier']['config'] = $config->getAll();
        if(!phorum_db_update_settings(array("mod_htmlpurifier"=>$PHORUM["mod_htmlpurifier"]))){
            $error="Database error while updating settings.";
        } else {
            echo "Settings Updated<br />";
        }
    }
}

// warning that's used by both messages
$warning = "
  <strong>Warning: Changing HTML Purifier's configuration will invalidate
  the cache. Expect to see a flurry of database activity after you change
  any of these settings.</strong>
";

if ($config_exists) {
    // clear out mod_htmlpurifier for housekeeping
    phorum_db_update_settings(array("mod_htmlpurifier"=>$PHORUM["mod_htmlpurifier"]));
    
    // politely tell user how to edit settings manually
?>
<div class="input-form-td-break">How to edit settings for HTML Purifier module</div>
<p>
  A <tt>config.php</tt> file exists in your <tt>mods/htmlpurifier/</tt>
  directory. This file contains your custom configuration: in order to
  change it, please navigate to that file and edit it accordingly.
</p>
<p>
  To use the web interface, delete <tt>config.php</tt> (or rename it to
  <tt>config.php.bak</tt>).
</p>
<p>
  <?php echo $warning ?>
</p>
<?php    
} else {
    // output form
    require_once './include/admin/PhorumInputForm.php';
    
    $frm_migrate = new PhorumInputForm ('', "post", "Migrate");
    $frm_migrate->hidden("module", "modsettings");
    $frm_migrate->hidden("mod", "htmlpurifier");
    $frm_migrate->hidden("migrate-sigs", "1");
    $frm_migrate->addbreak("Migrate user signatures to HTML");
    $frm_migrate->addMessage('This operation will migrate your users signatures
        to HTML. This process is irreversible and must only be performed once.
        Type in yes in the confirmation field to migrate.');
    if (!file_exists(dirname(__FILE__) . '/migrate.php')) {
        $frm_migrate->addMessage('Migration file does not exist, cannot migrate signatures.
            Please check <tt>migrate.bbcode.php</tt> on how to create an appropriate file.');
    } else {
        $frm_migrate->addrow('Confirm:', $frm_migrate->text_box("confirmation", ""));
    }
    $frm_migrate->show();
    
    echo '<br />';
    
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "htmlpurifier"); // this is the directory name that the Settings file lives in

    if (!empty($error)){
        echo "$error<br />";
    }

    $frm->addbreak("Edit settings for the HTML Purifier module");
    
    $frm->addMessage('<p>Click on directive links to read what each option does
    (links do not open in new windows).</p>
    <p>For more flexibility (for instance, you want to edit the full
    range of configuration directives), you can create a <tt>config.php</tt>
    file in your <tt>mods/htmlpurifier/</tt> directory. Doing so will,
    however, make the web configuration interface unavailable.</p>');
    
    require_once 'HTMLPurifier/Printer/ConfigForm.php';
    $htmlpurifier_form = new HTMLPurifier_Printer_ConfigForm('config', 'http://htmlpurifier.org/live/configdoc/plain.html#%s');
    $htmlpurifier_form->setTextareaDimensions(23, 7); // widen a little, since we have space
    
    $frm->addMessage($htmlpurifier_form->render($config, $directives, false));

    $frm->addMessage($warning);
    
    $frm->addrow('Reset to defaults:', $frm->checkbox("reset", "1", "", false));

    // hack to include extra styling
    echo '<style type="text/css">' . $htmlpurifier_form->getCSS() . '
    .hp-config {margin-left:auto;margin-right:auto;}
    </style>';
    $js = $htmlpurifier_form->getJavaScript();
    echo '<script type="text/javascript">'."<!--\n$js\n//-->".'</script>';
    
    $frm->show();
    
}
