<?php

// using _REQUEST because we accept GET and POST requests

$content = empty($_REQUEST['xml']) ? 'text/html' : 'application/xhtml+xml';
header("Content-type:$content;charset=UTF-8");

// prevent PHP versions with shorttags from barfing
echo '<?xml version="1.0" encoding="UTF-8" ?>
';

function getFormMethod() {
    return (isset($_REQUEST['post'])) ? 'post' : 'get';
}

if (empty($_REQUEST['strict'])) {
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>HTML Purifier Live Demo</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTML Purifier Live Demo</h1>
<?php

require_once '../../library/HTMLPurifier.auto.php';

if (!empty($_REQUEST['html'])) { // start result
    
    if (strlen($_REQUEST['html']) > 50000) {
        ?>
        <p>Request exceeds maximum allowed text size of 50kb.</p>
        <?php
    } else { // start main processing
    
    $html = get_magic_quotes_gpc() ? stripslashes($_REQUEST['html']) : $_REQUEST['html'];
    
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Core', 'TidyFormat', !empty($_REQUEST['tidy']));
    $config->set('HTML', 'Strict',     !empty($_REQUEST['strict']));
    $purifier = new HTMLPurifier($config);
    $pure_html = $purifier->purify($html);
    
?>
<p>Here is your purified HTML:</p>
<div style="border:5px solid #CCC;margin:0 10%;padding:1em;">
<?php if(getFormMethod() == 'get') { ?>
<div style="float:right;">
    <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10"
        alt="Valid XHTML 1.0 Transitional" height="31" width="88" style="border:0;" /></a>
</div>
<?php } ?>
<?php

echo $pure_html;

?>
<div style="clear:both;"></div>
</div>
<p>Here is the source code of the purified HTML:</p>
<pre><?php

echo htmlspecialchars($pure_html, ENT_COMPAT, 'UTF-8');

?></pre>
<?php
if (getFormMethod() == 'post') { // start POST validation notice
?>
<p>If you would like to validate the code with
<a href="http://validator.w3.org/#validate-by-input">W3C's
validator</a>, copy and paste the <em>entire</em> demo page's source.</p>
<?php
} // end POST validation notice

} // end main processing

// end result
} else {

?>
<p>Welcome to the live demo.  Enter some HTML and see how HTML Purifier
will filter it.</p>
<?php

}

?>
<form id="filter" action="demo.php<?php
echo '?' . getFormMethod();
if (isset($_REQUEST['profile']) || isset($_REQUEST['XDEBUG_PROFILE'])) {
    echo '&amp;XDEBUG_PROFILE=1';
} ?>" method="<?php echo getFormMethod();  ?>">
    <fieldset>
        <legend>HTML Purifier Input (<?php echo getFormMethod(); ?>)</legend>
        <textarea name="html" cols="60" rows="15"><?php

if (isset($html)) {
    echo htmlspecialchars(
            HTMLPurifier_Encoder::cleanUTF8($html), ENT_COMPAT, 'UTF-8');
}
        ?></textarea>
        <?php if (getFormMethod() == 'get') { ?>
            <p><strong>Warning:</strong> GET request method can only hold
                8129 characters (probably less depending on your browser).
                If you need to test anything
                larger than that, try the <a href="demo.php?post">POST form</a>.</p>
        <?php } ?>
        <?php if (extension_loaded('tidy')) { ?>
            <div>Nicely format output with Tidy? <input type="checkbox" value="1"
            name="tidy"<?php if (!empty($_REQUEST['tidy'])) echo ' checked="checked"'; ?> /></div>
        <?php } ?>
        <div>XHTML 1.0 Strict output? <input type="checkbox" value="1"
        name="strict"<?php if (!empty($_REQUEST['strict'])) echo ' checked="checked"'; ?> /></div>
        <div>Serve as application/xhtml+xml? (not for IE) <input type="checkbox" value="1"
        name="xml"<?php if (!empty($_REQUEST['xml'])) echo ' checked="checked"'; ?> /></div>
        <div>
            <input type="submit" value="Submit" name="submit" class="button" />
        </div>
    </fieldset>
</form>
<p>Return to <a href="http://hp.jpsband.org/">HTML Purifier's home page</a>.
Try the form in <a href="demo.php?get">GET</a> and <a href="demo.php?post">POST</a> request
flavors (GET is easy to validate with W3C, but POST allows larger inputs).</p>
</body>
</html>