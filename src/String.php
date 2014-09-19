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

class String extends Scope
{

    const CURRENT_POSITION = -1;

    /** @var string $data */
    private $data;

    /** @var int $position */
    private $position;

    public function __construct($data)
    {
        $this->position = 0;
        $this->data = $data;
        parent::__construct($this);
    }

    /**
     * Is this the end of file (string)?
     *
     * @return bool
     */
    public function eof()
    {
        return (!isset($this->data[$this->position]));
    }

    /**
     * Move string pointer.
     *
     * @param int $inc
     */
    public function inc($inc = 1)
    {
        $this->position += $inc;
    }

    /**
     * Temporary! Move string pointer to the end of line.
     */
    public function gotoNextEol()
    {
        $nextEol = strpos($this->data, PHP_EOL, $this->position);

        if (false === $nextEol) {
            $nextEol = strlen($this->data) - 1;
        }

        $this->position = $nextEol;
    }

    /**
     * Returns one character of the string.
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

}
