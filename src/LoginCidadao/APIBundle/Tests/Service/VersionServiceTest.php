<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Tests\Service;

use LoginCidadao\APIBundle\Service\VersionService;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class VersionServiceTest extends \PHPUnit_Framework_TestCase
{
    private function getSupportedVersions()
    {
        return [
            '1' => [
                '0' => ['0', '1', '2', '9'],
                '1' => ['0', '10'],
            ],
            '2' => [
                '0' => ['0', '1', '37'],
                '3' => ['20', '3', '0'],
            ],
            '3' => [
                '0' => ['0', '1', '5'],
            ],
        ];
    }

    private function getExpectedVersion($major, $minor, $patch)
    {
        return [
            'major' => $major,
            'minor' => $minor,
            'patch' => $patch,
        ];
    }

    /**
     * @param mixed $version
     * @return \PHPUnit_Framework_MockObject_MockObject|RequestStack
     */
    private function getRequestStack($version = null)
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        /** @var \PHPUnit_Framework_MockObject_MockObject|ParameterBag $attributes */
        $attributes = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
        $attributes->expects($this->any())
            ->method('get')->with('version')
            ->willReturn($version);

        if ($request instanceof Request) {
            $request->attributes = $attributes;
        }

        $requestStack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack');
        $requestStack->expects($this->any())->method('getCurrentRequest')->willReturn($request);

        return $requestStack;
    }

    private function getVersionService($version = null)
    {
        return new VersionService($this->getRequestStack($version), $this->getSupportedVersions());
    }

    public function testGetLatestVersion()
    {
        $expected = $this->getExpectedVersion(3, 0, 5);

        /** @var RequestStack $requestStack */
        $requestStack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack');

        $versionService = new VersionService($requestStack, $this->getSupportedVersions());
        $this->assertEquals($expected, $versionService->getLatestVersion());
    }

    public function testGetLatestVersionPartials()
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->getMock('Symfony\Component\HttpFoundation\RequestStack');

        $versionService = new VersionService($requestStack, $this->getSupportedVersions());

        $this->assertEquals(
            $this->getExpectedVersion(2, 3, 20),
            $versionService->getLatestVersion(2),
            'Expected 2.3.20'
        );
        $this->assertEquals(
            $this->getExpectedVersion(1, 1, 10),
            $versionService->getLatestVersion(1, 1),
            'Expected 1.1.10'
        );
    }

    public function testGetNonExistentVersion()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $versionService = $this->getVersionService();

        $versionService->getLatestVersion(150);
    }

    public function testGetLatestVersionWithInvalidInput()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $versionService = $this->getVersionService();

        $versionService->getLatestVersion(/** @scrutinizer ignore-type */'a');
    }

    public function testGetVersionFromRequest()
    {
        $versionService = $this->getVersionService('2');
        $this->assertEquals($this->getExpectedVersion(2, 3, 20), $versionService->getVersionFromRequest());

        $versionService = $this->getVersionService('2.0');
        $this->assertEquals($this->getExpectedVersion(2, 0, 37), $versionService->getVersionFromRequest());

        $versionService = $this->getVersionService('2.0.37');
        $this->assertEquals($this->getExpectedVersion(2, 0, 37), $versionService->getVersionFromRequest());
    }

    public function testGetString()
    {
        $versionService = $this->getVersionService();

        $this->assertEquals('1.0.0', $versionService->getString($this->getExpectedVersion(1, 0, 0)));
        $this->assertEquals('1.0', $versionService->getString([1, 0]));
        $this->assertEquals('4.9999999.8', $versionService->getString([4, 9999999, 8]));
    }
}
