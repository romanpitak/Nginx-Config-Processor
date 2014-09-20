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

class Directive
{

    /** @var Scope $childScope */
    private $childScope;

    /** @var Scope $parentScope */
    private $parentScope;


    /** @var string $text */
    private $text = '';

    private $name, $value;

    /**
     * @var \RomanPitak\Nginx\Config\String $configString
     */
    private $configString;


    /**
     * @param \RomanPitak\Nginx\Config\String $configString
     * @param Scope $parentScope
     */
    public function __construct(String $configString, Scope $parentScope)
    {
        if (!is_null($parentScope)) {
            $this->parentScope = $parentScope;
        }

        $this->configString = $configString;
        $this->run();
    }

    protected function run()
    {
        $configString = $this->configString;
        while (false === $configString->eof()) {

            $this->configString->skipComment();

            $c = $configString->getChar();

            if ('{' === $c) {
                $this->processText();
                $this->childScope = new Scope($configString);
                $configString->inc();
                break;
            }

            if (';' === $c) {
                $this->processText();
                break;
            }

            $this->text .= $c;

            $configString->inc();
        }
    }

    private function processText()
    {
        $found = false;

        $patternWithValue = '#^([a-z_]+) +([^;{]+)$#';
        if (1 === preg_match($patternWithValue, $this->text, $matches)) {
            $this->name = $matches[1];
            $this->value = rtrim($matches[2]);
            $found = true;
        }

        $patternWithoutValue = '#^([a-z_]+) *$#';
        if (1 === preg_match($patternWithoutValue, $this->text, $matches)) {
            $this->name = $matches[1];
            $this->value = null;
            $found = true;
        }
        if (false === $found) {
            throw new Exception('Text "' . $this->text . '" did not match pattern.');
        }

    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);
        $rs = $indent . $this->name . " " . $this->value;
        $rs .= isset($this->childScope) ? (" {\n" . $this->childScope->prettyPrint($indentLevel, $spacesPerIndent) . $indent .  "}\n") : ";\n";
        return $rs;
    }

}
