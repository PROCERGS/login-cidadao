<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OAuthBundle\Model;

use LoginCidadao\CoreBundle\Model\PersonInterface;

interface OrganizationInterface
{

    /**
     * @return int
     */
    public function getId();

    /**
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return PersonInterface[]
     */
    public function getMembers();

    /**
     * @param PersonInterface[] $members
     */
    public function setMembers(array $members);

    /**
     * @return boolean
     */
    public function isVerified();

    /**
     * @param boolean $verified
     */
    public function setVerified($verified);

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @param string $domain
     */
    public function setDomain($domain);

    /**
     * @return ClientInterface
     */
    public function getClients();

    /**
     *
     * @param ClientInterface[] $clients
     */
    public function setClients(array $clients);
}
