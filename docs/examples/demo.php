<?php

header('Content-type:text/html;charset=UTF-8');

?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>HTMLPurifier Live Demo</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTMLPurifier Live Demo</h1>
<?php

set_include_path('../../library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';

if (!empty($_POST['html'])) {
    
    $html = get_magic_quotes_gpc() ? stripslashes($_POST['html']) : $_POST['html'];
    
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Core', 'TidyFormat', !empty($_POST['tidy']));
    $purifier = new HTMLPurifier($config);
    $pure_html = $purifier->purify($html);
    
?>
<p>Here is your purified HTML:</p>
<div style="border:5px solid #CCC;margin:0 10%;padding:1em;">
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
    
} else {

?>
<p>Welcome to the live demo.  Enter some HTML and see how HTMLPurifier
will filter it.</p>
<?php

}

?>
<form name="filter" action="demo.php<?php
if (isset($_GET['profile']) || isset($_GET['XDEBUG_PROFILE'])) {
    echo '?XDEBUG_PROFILE=1';
} ?>" method="post">
    <fieldset>
        <legend>HTML</legend>
        <textarea name="html" cols="60" rows="15"><?php

if (isset($html)) {
    echo htmlspecialchars(
            HTMLPurifier_Encoder::cleanUTF8($html), ENT_COMPAT, 'UTF-8');
}
        ?></textarea>
        <div>Nicely format output with Tidy? <input type="checkbox" value="1"
        name="tidy"<?php if (!empty($_POST['tidy'])) echo ' checked="checked"'; ?> /></div>
        <div>
            <input type="submit" value="Submit" name="submit" class="button" />
        </div>
    </fieldset>
</form>
<p>Return to <a href="http://hp.jpsband.org/">HTMLPurifier's home page</a>.</p>
</body>
</html>