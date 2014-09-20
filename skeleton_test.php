<?php

namespace RomanPitak\Nginx\Config;

require_once('src/Exception.php');
require_once('src/StringProcessor.php');
require_once('src/Comment.php');
require_once('src/Directive.php');
require_once('src/Scope.php');
require_once('src/String.php');
require_once('src/File.php');

echo "START\n";

$f = new File('m1.conf');

echo $f . "\n\n";

echo "STOP\n";