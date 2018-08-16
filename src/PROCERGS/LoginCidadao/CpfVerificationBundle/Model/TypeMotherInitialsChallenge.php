<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CpfVerificationBundle\Model;

class TypeMotherInitialsChallenge extends AbstractChallenge
{
    public const CHALLENGE_NAME = 'type_mother_initials';

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return self::CHALLENGE_NAME;
    }
}
