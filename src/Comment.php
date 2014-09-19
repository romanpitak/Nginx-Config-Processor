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

class Comment extends StringProcessor
{

    protected function run()
    {
        $this->getConfigString()->gotoNextEol();
    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        return "# comment was here";
    }

}
