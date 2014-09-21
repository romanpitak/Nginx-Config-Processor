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

abstract class Printable
{
    abstract public function prettyPrint($indentLevel, $spacesPerIndent = 4);

    public function __toString()
    {
        return $this->prettyPrint(0);
    }
}