<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NfgProfileRepository extends EntityRepository
{
    /**
     * @param $cpf
     * @return null|object
     * @codeCoverageIgnore no need to test Doctrine's features
     */
    public function findByCpf($cpf)
    {
        return $this->findOneBy(['cpf' => $cpf]);
    }
}
