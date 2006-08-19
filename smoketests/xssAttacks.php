<?php

require_once('common.php');

?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>HTMLPurifier XSS Attacks Smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTMLPurifier XSS Attacks Smoketest</h1>
<p>XSS attacks are from
<a href="http://ha.ckers.org/xss.html">http://ha.ckers.org/xss.html</a>.</p>
<p>The last segment of tests regarding blacklisted websites is not
applicable at the moment, but when we add that functionality they'll be
relevant.</p>
<p>Most of the XSS broadcasts its presence by spawning an alert dialogue.</p>
<h2>Test</h2>
<?php

if (version_compare(PHP_VERSION, '5', '<')) exit('<p>Requires PHP 5.</p>');

$xml = simplexml_load_file('xssAttacks.xml');
$purifier = new HTMLPurifier();

?>
<!-- form is used so that we can use textareas and stay valid -->
<form method="post" action="xssAttacks.php">
<table>
<thead><tr><th>Name</th><th width="30%">Raw</th><th>Output</th><th>Render</th></tr></thead>
<tbody>
<?php

foreach ($xml->attack as $attack) {
    $code = $attack->code;
    // custom code for US-ASCII, which couldn't be expressed in XML without encoding
    if ($attack->name == 'US-ASCII encoding') $code = urldecode($code);
?>
    <tr>
        <td><?php echo escapeHTML($attack->name); ?></td>
        <td><textarea readonly="readonly" cols="20" rows="2"><?php echo escapeHTML($code); ?></textarea></td>
        <?php $pure_html = $purifier->purify($code); ?>
        <td><textarea readonly="readonly" cols="20" rows="2"><?php echo escapeHTML($pure_html); ?></textarea></td>
        <td><?php echo $pure_html ?></td>
    </tr>
<?php
}

?>
</tbody>
</table>
</form>
</body>
</html>