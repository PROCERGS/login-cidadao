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

interface TOSInterface
{

    /**
     * @return UserInterface
     */
    public function getAuthor();

    /**
     * @param UserInterface $user
     */
    public function setAuthor(UserInterface $author);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @return boolean
     */
    public function isFinal();

    /**
     *
     * @param boolean $isFinal
     */
    public function setFinal($isFinal = true);
}
