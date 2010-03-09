#!/bin/sh
hphp --log 3 --file-cache hphp-cache --generate-ffi 1 -o hphp-out -k 1 library/HTMLPurifier.stub.php
