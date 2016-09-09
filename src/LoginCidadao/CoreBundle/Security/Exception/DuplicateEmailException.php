<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Security\Exception;

class DuplicateEmailException extends \Exception
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    public function getService()
    {
        return $this->service;
    }
}
