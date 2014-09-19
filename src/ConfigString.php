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

class ConfigString
{

    const CURRENT_POSITION = 'current position';

    /** @var string $data */
    private $data;

    /** @var int $position */
    private $position;

    public function __construct($data)
    {
        $this->position = 0;
        $this->data = $data;
    }

    public function eof()
    {
        return (!isset($this->data[$this->position]));
        //return (!(strlen($this->data) > $this->position));
    }

    public function inc($inc = 1)
    {
        $this->position += $inc;
    }

    public function gotoNextEol()
    {
        $nextEol = strpos($this->data, PHP_EOL, $this->position);

        if (false === $nextEol) {
            $nextEol = strlen($this->data) - 1;
        }

        $this->position = $nextEol;
    }

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
