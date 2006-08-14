<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>HTMLPurifier XSS Attacks Smoketest</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1>HTMLPurifier XSS Attacks Smoketest</h1>
<p>XSS attacks courtsey of
<a href="http://ha.ckers.org/xss.html">http://ha.ckers.org/xss.html</a></p>
<p>The last segment of tests regarding blacklisted websites is not
applicable at the moment, but when we add that functionality they'll be
relevant.</p>
<?php

if (version_compare(PHP_VERSION, '5', '<')) exit('<p>Requires PHP 5.</p>');

set_include_path('../library' . PATH_SEPARATOR . get_include_path());
require_once 'HTMLPurifier.php';

$xml = simplexml_load_file('xssAttacks.xml');
$purifier = new HTMLPurifier();

?>
<form method="post" action="xssAttacks.php">
<table>
<thead><tr><th>Name</th><th width="30%">Raw</th><th>Output</th><th>Render</th></tr></thead>
<tbody>
<?php

foreach ($xml->attack as $attack) {
?>
    <tr>
        <td><?php echo htmlspecialchars($attack->name); ?></td>
        <td><textarea readonly="readonly" cols="20" rows="2"><?php echo htmlspecialchars($attack->code); ?></textarea></td>
        <?php $pure_html = $purifier->purify($attack->code); ?>
        <td><textarea readonly="readonly" cols="20" rows="2"><?php echo htmlspecialchars($pure_html); ?></textarea></td>
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