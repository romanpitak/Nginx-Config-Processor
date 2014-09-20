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

    /** @var string $name */
    private $name;

    /** @var string $value */
    private $value;

    /** @var Scope $childScope */
    private $childScope;

    /** @var Scope $parentScope */
    private $parentScope;

    /**
     * @param string $name
     * @param string $value
     * @param Scope $childScope
     * @param Scope $parentScope
     */
    public function __construct($name, $value, Scope $childScope = null, Scope $parentScope = null)
    {
        $this->name = $name;
        $this->value = $value;
        if (!is_null($childScope)) {
            $this->childScope = $childScope;
        }
        if (!is_null($parentScope)) {
            $this->parentScope = $parentScope;
        }
    }

    /**
     * @param \RomanPitak\Nginx\Config\String $configString
     * @param Scope $parentScope
     * @return self
     * @throws Exception
     */
    public static function fromString(String $configString, Scope $parentScope = null)
    {
        $text = '';
        while (false === $configString->eof()) {

            $configString->skipComment();

            $c = $configString->getChar();

            if ('{' === $c) {
                list($name, $value) = self::processText($text);
                $childScope = new Scope($configString);
                $configString->inc();
                return new Directive($name, $value, $childScope, $parentScope);
            }

            if (';' === $c) {
                list($name, $value) = self::processText($text);
                return new Directive($name, $value, null, $parentScope);
            }

            $text .= $c;

            $configString->inc();
        }
        throw new Exception('Could not create directive.');
    }

    private static function processText($text)
    {
        $found = false;
        $name = null;
        $value = null;

        $patternWithValue = '#^([a-z_]+) +([^;{]+)$#';
        if (1 === preg_match($patternWithValue, $text, $matches)) {
            $name = $matches[1];
            $value = rtrim($matches[2]);
            $found = true;
        }

        $patternWithoutValue = '#^([a-z_]+) *$#';
        if (1 === preg_match($patternWithoutValue, $text, $matches)) {
            $name = $matches[1];
            $value = null;
            $found = true;
        }
        if (false === $found) {
            throw new Exception('Text "' . $text . '" did not match pattern.');
        }

        return array($name, $value);

    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);
        $rs = $indent . $this->name . " " . $this->value;
        $rs .= isset($this->childScope) ? (" {\n" . $this->childScope->prettyPrint($indentLevel, $spacesPerIndent) . $indent . "}\n") : ";\n";
        return $rs;
    }

}
