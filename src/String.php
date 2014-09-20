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

class String
{
    const CURRENT_POSITION = -1;

    /** @var string $data */
    private $data;

    /** @var int $position */
    private $position;

    /**
     * @param string $data
     */
    public function __construct($data)
    {
        $this->position = 0;
        $this->data = $data;
    }

    /*
     * ========== Getters ==========
     */

    /**
     * Returns one character of the string.
     *
     * Does not move the string pointer. Use inc() to move the pointer after getChar().
     *
     * @param int $position If not specified, current character is returned.
     * @return string The current character (under the pointer).
     * @throws Exception When out of range
     */
    public function getChar($position = self::CURRENT_POSITION)
    {
        if (self::CURRENT_POSITION === $position) {
            $position = $this->position;
        }

        if (!is_int($position)) {
            throw new Exception('Position is not int. ' . gettype($position));
        }

        if ($this->eof()) {
            throw new Exception('Index out of range. Position: ' . $position . '.');
        }

        return $this->data[$position];
    }

    /**
     * Get the text from $position to the next end of line.
     *
     * Does not move the string pointer.
     *
     * @param int $position
     * @return string
     */
    public function getRestOfTheLine($position = self::CURRENT_POSITION)
    {
        if (self::CURRENT_POSITION === $position) {
            $position = $this->position;
        }
        $text = '';
        while((false === $this->eol($position)) && (false === $this->eof($position))) {
            $text = $this->getChar($position);
            $position++;
        }
        return $text;
    }

    /**
     * Is this the end of line?
     *
     * @param int $position
     * @return bool
     * @throws Exception
     */
    public function eol($position = self::CURRENT_POSITION)
    {
        return (("\r" === $this->getChar($position)) || ("\n" === $this->getChar($position)));
    }

    /**
     * Is this the end of file (string) or beyond?
     *
     * @param int $position
     * @return bool
     */
    public function eof($position = self::CURRENT_POSITION)
    {
        if (self::CURRENT_POSITION === $position) {
            $position = $this->position;
        }
        return (!isset($this->data[$position]));
    }

    /*
     * ========== Manipulators ==========
     */

    /**
     * Move string pointer.
     *
     * @param int $inc
     */
    public function inc($inc = 1)
    {
        $this->position += $inc;
    }

    /*
     * ========== Temporary ==========
     */

    public function skipComment()
    {
        if ('#' !== $this->getChar()) {
            return false;
        }

        Comment::fromString($this);
        return true;
    }
}
