<?php

namespace RomanPitak\Nginx\Config;

require_once('src/Exception.php');
require_once('src/Comment.php');
require_once('src/Directive.php');
require_once('src/Scope.php');
require_once('src/String.php');
require_once('src/File.php');

echo "START\n";

//*
$f = new File('m1.conf');
$s = Scope::fromString($f);
echo $s . "\n\n";
//*/

$d = new Directive('listen', 667);
echo $d->prettyPrint(0) . "\n\n";

$s2 = new Scope();
$s2->addDirective($d);
echo $s2 . "\n\n";

echo "STOP\n";