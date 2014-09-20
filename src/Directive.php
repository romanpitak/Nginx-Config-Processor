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
    private $childScope = null;

    /** @var Scope $parentScope */
    private $parentScope = null;

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
            $this->setChildScope($childScope);
        }
        if (!is_null($parentScope)) {
            $this->setParentScope($parentScope);
        }
    }

    /**
     * @param \RomanPitak\Nginx\Config\String $configString
     * @return self
     * @throws Exception
     */
    public static function fromString(String $configString)
    {
        $text = '';
        while (false === $configString->eof()) {

            $configString->skipComment();

            $c = $configString->getChar();

            if ('{' === $c) {
                list($name, $value) = self::processText($text);
                $directive = new Directive($name, $value);
                $childScope = Scope::fromString($configString);
                $childScope->setParentDirective($directive);
                $directive->setChildScope($childScope);
                $configString->inc();
                return $directive;
            }

            if (';' === $c) {
                list($name, $value) = self::processText($text);
                return new Directive($name, $value);
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

    /**
     * Get parent Scope
     *
     * @return Scope|null
     */
    public function getParentScope()
    {
        return $this->parentScope;
    }

    /**
     * Get child Scope.
     *
     * @return Scope|null
     */
    public function getChildScope()
    {
        return $this->childScope;
    }

    /**
     * Sets the parent Scope for this Directive.
     *
     * @param Scope $parentScope
     * @return $this
     */
    public function setParentScope(Scope $parentScope)
    {
        $this->parentScope = $parentScope;
        return $this;
    }

    /**
     * Sets the child Scope for this Directive.
     *
     * Sets the child Scope for this Directive and also
     * sets the $childScope->setParentDirective($this).
     *
     * @param Scope $childScope
     * @return $this
     */
    public function setChildScope(Scope $childScope)
    {
        $this->childScope = $childScope;

        if ($childScope->getParentDirective() !== $this) {
            $childScope->setParentDirective($this);
        }

        return $this;
    }

    /**
     * Pretty print with indentation.
     *
     * @param $indentLevel
     * @param int $spacesPerIndent
     * @return string
     */
    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);
        $rs = $indent . $this->name . " " . $this->value;
        $rs .= (!is_null($this->childScope)) ? (" {\n" . $this->childScope->prettyPrint($indentLevel, $spacesPerIndent) . $indent . "}\n") : ";\n";
        return $rs;
    }

}
