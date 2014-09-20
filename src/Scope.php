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

class Scope extends StringProcessor
{

    /** @var Directive[] $directives */
    private $directives = array();

    protected function run()
    {
        $configString = $this->getConfigString();
        while (false === $configString->eof()) {

            $this->skipComment();

            $c = $configString->getChar();

            if (('a' <= $c) && ('z' >= $c)) {
                $this->directives[] = new Directive($configString, $this);
                continue;
            }

            if ('}' === $configString->getChar()) {
                break;
            }

            $configString->inc();
        }
    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $rs = "";
        foreach ($this->directives as $directive) {
            $rs .= $directive->prettyPrint($indentLevel + 1, $spacesPerIndent);
        }

        return $rs;
    }

}
