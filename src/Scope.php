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

class Scope extends Printable
{
    /** @var Directive $parentDirective */
    private $parentDirective = null;

    /** @var Directive[] $directives */
    private $directives = array();

    /** @var Printable[] $printables */
    private $printables = array();

    /*
     * ========== Factories ==========
     */

    /**
     * Create new Scope from the configuration string.
     *
     * @param \RomanPitak\Nginx\Config\String $configString
     * @return Scope
     * @throws Exception
     */
    public static function fromString(String $configString)
    {
        $scope = new Scope();
        while (false === $configString->eof()) {

            if (true === $configString->isEmptyLine()) {
                $scope->addPrintable(EmptyLine::fromString($configString));
            }

            $c = $configString->getChar();

            if ('#' === $c) {
                $scope->addPrintable(Comment::fromString($configString));
                continue;
            }

            if (('a' <= $c) && ('z' >= $c)) {
                $scope->addDirective(Directive::fromString($configString));
                continue;
            }

            if ('}' === $configString->getChar()) {
                break;
            }

            $configString->inc();
        }
        return $scope;
    }

    /**
     * Create new Scope from a file.
     *
     * @param $filePath
     * @return Scope
     */
    public static function fromFile($filePath)
    {
        return self::fromString(new File($filePath));
    }

    /*
     * ========== Getters ==========
     */

    /**
     * Get parent Directive.
     *
     * @return Directive|null
     */
    public function getParentDirective()
    {
        return $this->parentDirective;
    }

    /*
     * ========== Setters ==========
     */

    /**
     * Add a Directive to the list of this Scopes directives
     *
     * Adds the Directive and sets the Directives parent Scope to $this.
     *
     * @param Directive $directive
     * @return $this
     */
    public function addDirective(Directive $directive)
    {
        if ($directive->getParentScope() !== $this) {
            $directive->setParentScope($this);
        }

        $this->directives[] = $directive;
        $this->addPrintable($directive);

        return $this;
    }

    /**
     * Add printable element.
     *
     * @param Printable $printable
     */
    private function addPrintable(Printable $printable)
    {
        $this->printables[] = $printable;
    }

    /**
     * Set parent directive for this Scope.
     *
     * Sets parent directive for this Scope and also
     * sets the $parentDirective->setChildScope($this)
     *
     * @param Directive $parentDirective
     * @return $this
     */
    public function setParentDirective(Directive $parentDirective)
    {
        $this->parentDirective = $parentDirective;

        if ($parentDirective->getChildScope() !== $this) {
            $parentDirective->setChildScope($this);
        }

        return $this;
    }

    /*
     * ========== Printable ==========
     */

    /**
     * Pretty print with indentation.
     *
     * @param $indentLevel
     * @param int $spacesPerIndent
     * @return string
     */
    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $rs = "";
        foreach ($this->printables as $printable) {
            $rs .= $printable->prettyPrint($indentLevel + 1, $spacesPerIndent);
        }

        return $rs;
    }

    public function __toString()
    {
        return $this->prettyPrint(-1);
    }
}
