HTML Purifier
=============

[![Latest Stable Version](https://poser.pugx.org/ezyang/htmlpurifier/v/stable.svg)](https://packagist.org/packages/ezyang/htmlpurifier) [![Total Downloads](https://poser.pugx.org/ezyang/htmlpurifier/downloads.svg)](https://packagist.org/packages/ezyang/htmlpurifier) [![Latest Unstable Version](https://poser.pugx.org/ezyang/htmlpurifier/v/unstable.svg)](https://packagist.org/packages/ezyang/htmlpurifier) [![License](https://poser.pugx.org/ezyang/htmlpurifier/license.svg)](https://packagist.org/packages/ezyang/htmlpurifier)


HTML Purifier is an HTML filtering solution that uses a unique combination
of robust whitelists and agressive parsing to ensure that not only are
XSS attacks thwarted, but the resulting HTML is standards compliant.

HTML Purifier is oriented towards richly formatted documents from
untrusted sources that require CSS and a full tag-set.  This library can
be configured to accept a more restrictive set of tags, but it won't be
as efficient as more bare-bones parsers. It will, however, do the job
right, which may be more important.

Places to go:

* See INSTALL for a quick installation guide
* See docs/ for developer-oriented documentation, code examples and
  an in-depth installation guide.
* See WYSIWYG for information on editors like TinyMCE and FCKeditor

HTML Purifier can be found on the web at: [http://htmlpurifier.org/](http://htmlpurifier.org/)

    vim: et sw=4 sts=4

## Instalation

Package available on [Composer](https://packagist.org/packages/ezyang/htmlpurifier).

If you're using Composer to manage dependencies, you can use

    $ composer require "ezyang/htmlpurifier": "dev-master"
