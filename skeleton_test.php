<?php

namespace RomanPitak\Nginx\Config;

require_once('src/Exception.php');
require_once('src/StringProcessor.php');
require_once('src/Comment.php');
require_once('src/Directive.php');
require_once('src/Scope.php');
require_once('src/ConfigString.php');
require_once('src/ConfigFile.php');

echo "START\n";

$f = new ConfigFile('m1.conf');

$s = new Scope($f);

echo $s;

echo "STOP\n";