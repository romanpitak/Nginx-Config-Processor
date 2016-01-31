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

class IncludeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Fail on non existing file
     *
     */
    public function testInclude()
    {
        print Scope::fromFile('/etc/nginx/nginx.conf');
    }

}
