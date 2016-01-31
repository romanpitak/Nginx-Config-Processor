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

class EmptyLineTest extends \PHPUnit_Framework_TestCase
{

    public function testCanBeConstructed()
    {
        $emptyLine = new EmptyLine();
        $this->assertInstanceOf('\\RomanPitak\\Nginx\\Config\\EmptyLine', $emptyLine);
        return $emptyLine;
    }

    /**
     * @depends testCanBeConstructed
     *
     * @param EmptyLine $emptyLine
     */
    public function testPrettyPrint(EmptyLine $emptyLine)
    {
        $this->assertEquals("\n", $emptyLine->prettyPrint(0));
    }

}
