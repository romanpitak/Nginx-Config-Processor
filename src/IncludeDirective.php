<?php

/**
 * This file is part of the Nginx Config Processor package.
 *
 * (c) Roman Piták <roman@pitak.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace RomanPitak\Nginx\Config;

class IncludeDirective extends Directive
{
    public function __construct(
        $fileName,
        $includePath,
        Scope $childScope = null,
        Scope $parentScope = null,
        Comment $comment = null
    )
    {
        if (1 == preg_match('#^(?:\/|\\\\|\w:\\\\|\w:\/).*$#', $fileName)) {
            $childScope = Scope::fromFile($fileName);
        } else {
            $filePath = realpath(implode(DIRECTORY_SEPARATOR, [$includePath, $fileName]));
            $childScope = Scope::fromFile($filePath);
        }
        $childScope->setParentDirective($this);
        parent::__construct('include', $fileName, $childScope, $parentScope, $comment);
    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        return $this->getChildScope()->prettyPrint($indentLevel - 1, $spacesPerIndent);
    }
}
