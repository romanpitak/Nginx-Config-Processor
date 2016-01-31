<?php
/**
 * This file is part of the romanpitak/nginx-config-processor package.
 *
 * (c) Roman Piták <roman@pitak.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RomanPitak\Nginx\Config;

class FileTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Fail on non existing file
     *
     * @expectedException \RomanPitak\Nginx\Config\Exception
     */
    public function testCannotRead()
    {
        new File('this_file_does_not_exist.txt');
    }

}
