<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\APIBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class VersionService
{
    /** @var array */
    private $supportedVersions;

    /**
     * VersionService constructor.
     * @param array $supportedVersions
     */
    public function __construct(array $supportedVersions)
    {
        $this->supportedVersions = $supportedVersions;
    }

    /**
     * @param int|null $major
     * @param int|null $minor
     * @param int|null $patch
     * @return string
     */
    public function getLatestVersion($major = null, $minor = null, $patch = null)
    {
        if ($major === null) {
            $major = max(array_keys($this->supportedVersions));
        }
        if ($minor === null) {
            $minor = max(array_keys($this->supportedVersions[$major]));
        }
        if ($patch === null) {
            $patch = max($this->supportedVersions[$major][$minor]);
        }

        if (false === array_key_exists($major, $this->supportedVersions)
            || false === array_key_exists($minor, $this->supportedVersions[$major])
            || false === array_search($patch, $this->supportedVersions[$major][$minor])) {
            throw new \InvalidArgumentException("Invalid API version");
        }
        $version = implode('.', [$major, $minor, $patch]);

        return $version;
    }

    /**
     * @param Request $request
     * @return string
     */
    public function getVersionFromRequest(Request $request)
    {
        $pathVersion = $request->attributes->get('version');
        $version = explode('.', $pathVersion, 3);

        $major = $minor = $patch = null;
        if (array_key_exists(0, $version)) {
            $major = $version[0];
        }
        if (array_key_exists(1, $version)) {
            $minor = $version[1];
        }
        if (array_key_exists(2, $version)) {
            $patch = $version[2];
        }

        return $this->getLatestVersion($major, $minor, $patch);
    }
}
