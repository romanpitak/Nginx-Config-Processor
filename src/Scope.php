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

class Scope
{

    /** @var Directive[] $directives */
    private $directives = array();


    /**
     * @var \RomanPitak\Nginx\Config\String $configString
     */
    private $configString;


    /**
     * @param \RomanPitak\Nginx\Config\String $configString
     */
    public function __construct(String $configString)
    {
        $this->configString = $configString;
        $this->run();
    }

    protected function run()
    {
        $configString = $this->configString;
        while (false === $configString->eof()) {

            $this->configString->skipComment();

            $c = $configString->getChar();

            if (('a' <= $c) && ('z' >= $c)) {
                $this->directives[] = Directive::fromString($configString, $this);
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

    public function __toString()
    {
        return $this->prettyPrint(-1);
    }

}
