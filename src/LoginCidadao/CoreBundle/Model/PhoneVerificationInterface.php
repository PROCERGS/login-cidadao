<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Model;

interface PhoneVerificationInterface
{
    /**
     * Get the phone's owner
     * @return PersonInterface
     */
    public function getPerson();

    /**
     * @param PersonInterface $person
     * @return PhoneVerificationInterface
     */
    public function setPerson(PersonInterface $person);

    /**
     * Get the phone being verified
     * @return mixed
     */
    public function getPhone();

    /**
     * @param mixed $phone
     * @return PhoneVerificationInterface
     */
    public function setPhone($phone);

    /**
     * Get the date when the phone was validated
     * @return \DateTime
     */
    public function getVerifiedAt();

    /**
     * @param \DateTime $verifiedAt
     * @return PhoneVerificationInterface
     */
    public function setVerifiedAt($verifiedAt);
}
