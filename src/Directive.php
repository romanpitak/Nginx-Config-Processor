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
    public function __construct($name, $value, Scope $childScope = null, Scope $parentScope = null, Comment $comment = null)
    {
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
     * @param \RomanPitak\Nginx\Config\String $configString
     * @return self
     * @throws Exception
     */
    public static function fromString(String $configString)
    {
        $text = '';
        while (false === $configString->eof()) {

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
                $configString->inc();
                list($name, $value) = self::processText($text);
                $directive = new Directive($name, $value);

                // check for associated comment on the rest of the line
                $restOfTheLine = $configString->getRestOfTheLine();
                if (1 === preg_match('/^\s*#/', $restOfTheLine)) {
                    $commentPosition = strpos($restOfTheLine, '#');
                    $configString->inc($commentPosition);
                    $directive->setComment(Comment::fromString($configString));
                }

                return $directive;
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

        $rs = $indent . $this->name . " " . $this->value;

        if (is_null($this->getChildScope())) {
            $rs .= ";";
        } else {
            $rs .= " {\n" . $this->childScope->prettyPrint($indentLevel, $spacesPerIndent) . $indent . "}";
        }

        if (false === $this->hasComment()) {
            $rs .= "\n";
        } else {
            if (false === $this->getComment()->isMultiline()) {
                $rs .= " " . $this->comment->prettyPrint(0,0);
            } else {
                $comment = $this->getComment()->prettyPrint($indentLevel, $spacesPerIndent);
                $rs = $comment . "\n" . $rs;
            }
        }

        return $rs;
    }
}
