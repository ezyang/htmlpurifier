<?php

require_once 'common.php';

// todo : modularize the HTML in to separate files

echo '<?xml version="1.0" encoding="UTF-8" ?>';
?><!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <title>HTML Purifier UTF-8 Smoketest</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="allElements.css" type="text/css" />
</head>
<body>
<?php

$config = HTMLPurifier_Config::createDefault();
$config->set('Attr', 'EnableID', true);

$purifier = new HTMLPurifier($config);
echo $purifier->purify(file_get_contents('allElements.html'));

?>
</body>
</html>