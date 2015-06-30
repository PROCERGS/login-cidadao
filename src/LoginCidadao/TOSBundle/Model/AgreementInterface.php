<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\TOSBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface AgreementInterface
{

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user);

    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @param TOSInterface $termsOfService
     */
    public function setTermsOfService(TOSInterface $termsOfService);

    /**
     * @return TOSInterface
     */
    public function getTermsOfService();

    /**
     * @return \DateTime
     */
    public function getAgreedAt();
}
