<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Tests;

class TestsUtil
{
    public static function getRouter(\PHPUnit_Framework_TestCase $testCase)
    {
        $router = $testCase->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($testCase->any())->method('generate')
            ->willReturnCallback(function ($routeName) {
                return $routeName;
            });

        return $router;
    }
}
