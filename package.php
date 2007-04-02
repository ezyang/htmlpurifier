<?php

set_time_limit(0);

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_PRINT);
$pkg = new PEAR_PackageFileManager2;

$pkg->setOptions(
    array(
        'baseinstalldir' => '/',
        'packagefile' => 'package2.xml',
        'packagedirectory' => dirname(__FILE__) . '/library',
        'filelistgenerator' => 'file',
        'include' => array('*'),
        'ignore' => array('HTMLPurifier.auto.php'),
    )
);

$pkg->setPackage('HTMLPurifier');
$pkg->setLicense('LGPL', 'http://www.gnu.org/licenses/lgpl.html');
$pkg->setSummary('Standards-compliant HTML filter');
$pkg->setDescription(
    'HTML Purifier is an HTML filter that will remove all malicious code
    (better known as XSS) with a thoroughly audited, secure yet permissive
    whitelist and will also make sure your documents are standards
    compliant.'
);

$pkg->addMaintainer('lead', 'edwardzyang', 'Edward Z. Yang', 'htmlpurifier@jpsband.org', 'yes');

$pkg->setChannel('hp.jpsband.org');
$pkg->setAPIVersion('1.5');
$pkg->setAPIStability('stable');
$pkg->setReleaseVersion('1.5.0');
$pkg->setReleaseStability('stable');

$pkg->addRelease();

$pkg->setNotes('Major bugs were fixed and some major internal refactoring was undertaken. The visible changes include XHTML 1.1-style modularization of HTMLDefinition, rudimentary internationalization, and a fix for a fatal error when the PHP4 DOM XML extension was loaded. The x subtag is now allowed in language codes. Element by element AllowedAttribute declaration is now possible for global attributes. Instead of *.class, you can write span.class. The old syntax still works, and enables the attribute for all elements.');
$pkg->setPackageType('php');

$pkg->setPhpDep('4.3.9');
$pkg->setPearinstallerDep('1.4.3');

$pkg->generateContents();

$compat =& $pkg->exportCompatiblePackageFile1();
$compat->writePackageFile();
$pkg->writePackageFile();

?>