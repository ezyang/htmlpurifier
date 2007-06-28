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

// save settings
if(!empty($_POST)){
    if ($config_exists) {
        echo "Cannot update settings, <code>mods/htmlpurifier/config.php</code> already exists. To change
        settings, edit that file. To use the web form, delete that file.<br />";
    } else {
        $config->mergeArrayFromForm($_POST, 'config', $directives);
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
    
    $frm = new PhorumInputForm ("", "post", "Save");
    $frm->hidden("module", "modsettings");
    $frm->hidden("mod", "htmlpurifier"); // this is the directory name that the Settings file lives in

    if (!empty($error)){
        echo "$error<br />";
    }

    $frm->addbreak("Edit settings for the HTML Purifier module");
    
    $frm->addMessage('<p>Click on directive links to read what each option does.
    <strong>Warning: This will navigate you to a new page.</strong></p>
    <p>For more flexibility (for instance, you want to edit the full
    range of configuration directives), you can create a <tt>config.php</tt>
    file in your <tt>mods/htmlpurifier/</tt> directory. Doing so will,
    however, make the web configuration interface unavailable.</p>');
    
    require_once 'HTMLPurifier/Printer/ConfigForm.php';
    $htmlpurifier_form = new HTMLPurifier_Printer_ConfigForm('config', 'http://htmlpurifier.org/live/configdoc/plain.html#%s');
    $frm->addMessage($htmlpurifier_form->render($config, $directives, false));

    $frm->addMessage($warning);

    // hack to include extra styling
    echo '<style type="text/css">' . $htmlpurifier_form->getCSS() . '
    .hp-config {margin-left:auto;margin-right:auto;}
    </style>';
    $js = $htmlpurifier_form->getJavaScript();
    echo '<script type="text/javascript">'."<!--\n$js\n//-->".'</script>';
    
    $frm->show();
}
