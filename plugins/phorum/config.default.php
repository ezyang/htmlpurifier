<?php

if(!defined("PHORUM")) exit;

// default HTML Purifier configuration settings
$config->set('HTML', 'Allowed',
  // definitely needed
  'a[href|title],blockquote[cite],b,pre,i,p,'.
  // common semantic markup
  'del,ins,strong,em,'.
  // commmon presentational markup
  's,strike,sub,sup,u,br,tt,div[class],'. // div because bbcode [quote] uses it
  // uncommon semantic markup
  'abbr[title],acronym[title],caption,code,dfn,cite,kbd,var,'.
  // lists
  'dd,dl,dt,ul,li,ol,'.
  // tables
  'table,tr,tbody,thead,tfoot,td,th');
$config->set('AutoFormat', 'AutoParagraph', true);
$config->set('AutoFormat', 'Linkify', true);
$config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
$config->set('Core', 'AggressivelyFixLt', true);
$config->set('Core', 'Encoding', 'iso-8859-1'); // we'll change this eventually

