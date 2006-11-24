<?php

require_once 'common.php'; // load library

require_once 'HTMLPurifier/Printer/HTMLDefinition.php';
require_once 'HTMLPurifier/Printer/CSSDefinition.php';

$config = HTMLPurifier_Config::createDefault();

// you can do custom configuration!
if (file_exists('printDefinition.settings.php')) {
    include 'printDefinition.settings.php';
}

$get = $_GET;
foreach ($_GET as $key => $value) {
    if (!strncmp($key, 'Null_', 5) && !empty($value)) {
        unset($get[substr($key, 5)]);
        unset($get[$key]);
    }
}

@$config->loadArray($get);

$printer_html_definition = new HTMLPurifier_Printer_HTMLDefinition();
$printer_css_definition  = new HTMLPurifier_Printer_CSSDefinition();

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>HTML Purifier Printer Smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">
        form table {margin:1em auto;}
        form th {text-align:right;padding-right:1em;}
        .HTMLPurifier_Printer table {border-collapse:collapse;
            border:1px solid #000; width:600px;
            margin:1em auto;font-family:sans-serif;font-size:75%;}
        .HTMLPurifier_Printer td, .HTMLPurifier_Printer th {padding:3px;
            border:1px solid #000;background:#CCC; vertical-align: baseline;}
        .HTMLPurifier_Printer th {text-align:left;background:#CCF;width:20%;}
        .HTMLPurifier_Printer caption {font-size:1.5em; font-weight:bold;
            width:100%;}
        .HTMLPurifier_Printer .heavy {background:#99C;text-align:center;}
    </style>
    <script type="text/javascript">
        function toggleWriteability(id_of_patient, checked) {
            document.getElementById(id_of_patient).disabled = checked;
        }
    </script>
</head>
<body>
<h1>HTML Purifier Printer Smoketest</h1>
<p>Pretty-print a definition and check out its contents. You can
also twiddle with the configuration settings to see how a directive
influences the internal behavior of the object.</p>
<h2>Modify configuration</h2>

<p>You can specify an array by typing in a comma-separated
list of items, HTML Purifier will take care of the rest (including
transformation into a real array list or a lookup table). If a
directive can be set to null, that usually means that the feature
is disabled when it is null (not that, say, no tags are allowed).</p>

<form id="edit-config" method="get" action="printDefinition.php">
<table>
<?php
    $directives = $config->getBatch('HTML');
    // can't handle hashes
    foreach ($directives as $key => $value) {
        $directive = "HTML.$key";
        if (is_array($value)) {
            $keys = array_keys($value);
            if ($keys === array_keys($keys)) {
                $value = implode(',', $keys);
            } else {
                $new_value = '';
                foreach ($value as $name => $bool) {
                    if ($bool !== true) continue;
                    $new_value .= "$name,";
                }
                $value = rtrim($new_value, ',');
            }
        }
        $allow_null = $config->def->info['HTML'][$key]->allow_null;
?>
<tr>
<th>
    <a href="http://hp.jpsband.org/live/configdoc/plain.html#<?php echo $directive ?>">
        %<?php echo $directive; ?>
    </a>
</th>
<td>
<?php if (is_bool($value)) { ?>
    Yes <input type="radio" name="<?php echo $directive; ?>" value="1"<?php if ($value) { ?> checked="checked"<?php } ?> /> &nbsp;
    No <input type="radio" name="<?php echo $directive; ?>" value="0"<?php if (!$value) { ?> checked="checked"<?php } ?> />
<?php } else { ?>
    <?php if($allow_null) { ?>
        Null/Disabled <input
                type="checkbox"
                value="1"
                onclick="toggleWriteability('<?php echo $directive ?>',checked)"
                name="Null_<?php echo $directive; ?>"
                <?php if ($value === null) { ?> checked="checked"<?php } ?>
              /> or <br />
    <?php } ?>
    <input
        type="text"
        id="<?php echo $directive; ?>"
        name="<?php echo $directive; ?>"
        value="<?php echo escapeHTML($value); ?>"
        <?php if($value === null) {echo 'disabled="disabled"';} ?>
    />
<?php } ?>
</td>
</tr>
<?php
    }
?>
<tr>
    <td colspan="2" style="text-align:right;">
        [<a href="printDefinition.php">Reset</a>]
        <input type="submit" value="Submit" />
    </td>
</tr>
</table>
</form>
<h2>HTMLDefinition</h2>
<?php echo $printer_html_definition->render($config) ?>
<h2>CSSDefinition</h2>
<?php echo $printer_css_definition->render($config) ?>
</body>
</html>