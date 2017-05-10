<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LoginCidadao\PhoneVerificationBundle\Model\PhoneVerificationInterface;

/**
 * @codeCoverageIgnore
 */
class SentVerificationRepository extends EntityRepository
{
    public function getLastVerificationSent(PhoneVerificationInterface $phoneVerification)
    {
        return $this->findOneBy(
            ['phone' => $phoneVerification->getPhone()],
            ['sentAt' => 'DESC']
        );
    }
}
