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

class Directive extends Printable
{
    /** @var string $name */
    private $name;

    /** @var string $value */
    private $value;

    /** @var Scope $childScope */
    private $childScope = null;

    /** @var Scope $parentScope */
    private $parentScope = null;

    /** @var Comment $comment */
    private $comment = null;

    /**
     * @param string $name
     * @param string $value
     * @param Scope $childScope
     * @param Scope $parentScope
     * @param Comment $comment
     */
    public function __construct(
        $name,
        $value = null,
        Scope $childScope = null,
        Scope $parentScope = null,
        Comment $comment = null
    ) {
        $this->name = $name;
        $this->value = $value;
        if (!is_null($childScope)) {
            $this->setChildScope($childScope);
        }
        if (!is_null($parentScope)) {
            $this->setParentScope($parentScope);
        }
        if (!is_null($comment)) {
            $this->setComment($comment);
        }
    }

    /*
     * ========== Factories ==========
     */

    /**
     * Provides fluid interface.
     *
     * @param $name
     * @param null $value
     * @param Scope $childScope
     * @param Scope $parentScope
     * @param Comment $comment
     * @return Directive
     */
    public static function create(
        $name,
        $value = null,
        Scope $childScope = null,
        Scope $parentScope = null,
        Comment $comment = null
    ) {
        return new self($name, $value, $childScope, $parentScope, $comment);
    }

    /**
     * @param \RomanPitak\Nginx\Config\Text $configString
     * @return self
     * @throws Exception
     */
    public static function fromString(Text $configString)
    {
        $text = '';
        while (false === $configString->eof()) {
            $char = $configString->getChar();
            if ('{' === $char) {
                return self::newDirectiveWithScope($text, $configString);
            }
            if (';' === $char) {
                return self::newDirectiveWithoutScope($text, $configString);
            }
            $text .= $char;
            $configString->inc();
        }
        throw new Exception('Could not create directive.');
    }

    private static function newDirectiveWithScope(
        $nameString,
        Text $scopeString
    ) {
        $scopeString->inc();
        list($name, $value) = self::processText($nameString);
        $directive = new Directive($name, $value);

        $comment = self::checkRestOfTheLineForComment($scopeString);
        if (false !== $comment) {
            $directive->setComment($comment);
        }

        $childScope = Scope::fromString($scopeString);
        $childScope->setParentDirective($directive);
        $directive->setChildScope($childScope);

        $scopeString->inc();

        $comment = self::checkRestOfTheLineForComment($scopeString);
        if (false !== $comment) {
            $directive->setComment($comment);
        }

        return $directive;
    }

    private static function newDirectiveWithoutScope(
        $nameString,
        Text $configString
    ) {
        $configString->inc();
        list($name, $value) = self::processText($nameString);
        $directive = new Directive($name, $value);

        $comment = self::checkRestOfTheLineForComment($configString);
        if (false !== $comment) {
            $directive->setComment($comment);
        }

        return $directive;
    }

    private static function checkRestOfTheLineForComment(Text $configString)
    {
        $restOfTheLine = $configString->getRestOfTheLine();
        if (1 !== preg_match('/^\s*#/', $restOfTheLine)) {
            return false;
        }

        $commentPosition = strpos($restOfTheLine, '#');
        $configString->inc($commentPosition);
        return Comment::fromString($configString);
    }

    private static function processText($text)
    {
        $result = self::checkKeyValue($text);
        if (is_array($result)) {
            return $result;
        }
        $result = self::checkKey($text);
        if (is_array($result)) {
            return $result;
        }
        throw new Exception('Text "' . $text . '" did not match pattern.');
    }

    private static function checkKeyValue($text) {
        if (1 === preg_match('#^([a-z][a-z0-9._/+-]*)\s+([^;{]+)$#', $text, $matches)) {
            return array($matches[1], rtrim($matches[2]));
        }
        return false;
    }

    private static function checkKey($text) {
        if (1 === preg_match('#^([a-z][a-z0-9._/+-]*)\s*$#', $text, $matches)) {
            return array($matches[1], null);
        }
        return false;
    }

    /*
     * ========== Getters ==========
     */

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
     * Get the associated Comment for this Directive.
     *
     * @return Comment
     */
    public function getComment()
    {
        if (is_null($this->comment)) {
            $this->comment = new Comment();
        }
        return $this->comment;
    }

    /**
     * Does this Directive have a Comment associated with it?
     *
     * @return bool
     */
    public function hasComment()
    {
        return (!$this->getComment()->isEmpty());
    }

    /*
     * ========== Setters ==========
     */

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
     * Set the associated Comment object for this Directive.
     *
     * This will overwrite the existing comment.
     *
     * @param Comment $comment
     * @return $this
     */
    public function setComment(Comment $comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Set the comment text for this Directive.
     *
     * This will overwrite the existing comment.
     *
     * @param $text
     * @return $this
     */
    public function setCommentText($text)
    {
        $this->getComment()->setText($text);
        return $this;
    }

    /*
     * ========== Printing ==========
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
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);

        $resultString = $indent . $this->name;
        if (!is_null($this->value)) {
            $resultString .= " " . $this->value;
        }

        if (is_null($this->getChildScope())) {
            $resultString .= ";";
        } else {
            $resultString .= " {";
        }

        if (false === $this->hasComment()) {
            $resultString .= "\n";
        } else {
            if (false === $this->getComment()->isMultiline()) {
                $resultString .= " " . $this->comment->prettyPrint(0, 0);
            } else {
                $comment = $this->getComment()->prettyPrint($indentLevel, $spacesPerIndent);
                $resultString = $comment . $resultString;
            }
        }

        if (!is_null($this->getChildScope())) {
            $resultString .= "" . $this->childScope->prettyPrint($indentLevel, $spacesPerIndent) . $indent . "}\n";
        }

        return $resultString;
    }
}
