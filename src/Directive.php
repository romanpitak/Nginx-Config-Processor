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

class Directive extends StringProcessor
{

    /** @var Scope $scope */
    private $scope;

    /** @var string $text */
    private $text = '';

    private $name, $value;

    protected function run()
    {
        $configString = $this->getConfigString();
        while (false === $configString->eof()) {

            $this->skipComment();

            $c = $configString->getChar();

            if ('{' === $c) {
                $this->processText();
                $this->scope = new Scope($configString);
                $configString->inc();
                break; // this continue is not important
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
            throw new Exception('Did not match pattern.');
        }

    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);
        $rs = $indent . $this->name . " " . $this->value;
        $rs .= isset($this->scope) ? (" {\n" . $this->scope->prettyPrint($indentLevel, $spacesPerIndent) . $indent .  "}\n") : ";\n";
        return $rs;
    }

}
