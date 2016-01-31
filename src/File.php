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

class File extends Text
{
    /** @var string $inFilePath */
    private $inFilePath;

    /**
     * @param $filePath string Name of the conf file (or full path).
     * @throws Exception
     */
    public function __construct($filePath)
    {
        $this->inFilePath = $filePath;

        $contents = @file_get_contents($this->inFilePath);

        if (false === $contents) {
            throw new Exception('Cannot read file "' . $this->inFilePath . '".');
        }

        parent::__construct($contents);
    }
}
