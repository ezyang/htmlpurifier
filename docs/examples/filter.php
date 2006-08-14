<!DOCTYPE html 
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
    
    $purifier = new HTMLPurifier();
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

echo htmlspecialchars($pure_html);

?></pre>
<?php
    
} else {

?>
<p>Welcome to the live demo.  Enter some HTML and see how HTMLPurifier
will filter it.</p>
<?php

}

?>
<form name="filter" action="filter.php" method="post">
    <fieldset>
        <legend>HTML</legend>
        <textarea name="html" cols="60" rows="15"><?php

if (isset($html)) echo htmlspecialchars($html);

        ?></textarea>
        <div>
            <input type="submit" value="Submit" name="submit" class="button" />
        </div>
    </fieldset>
</form>
</body>
</html>