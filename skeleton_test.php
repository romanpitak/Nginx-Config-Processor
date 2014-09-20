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
echo Scope::fromFile('m1.conf') . "\n\n";
//*/

$d = new Directive('listen', 667);
echo $d->prettyPrint(0) . "\n\n";

$s2 = new Scope();
$s2->addDirective($d);
echo $s2 . "\n\n";

echo new Comment("This is a simple comment.") . "\n\n";

echo new Comment("This \nis \r\na multi
line " . PHP_EOL . "comment.") . "\n\n";

$dc = new Directive('deny', 'all');
$dc->setCommentText('Directive with a comment');
echo $dc . "\n\n";

$dc->setCommentText('Directive with a multi
line comment');
echo $dc . "\n\n";

echo "STOP\n";