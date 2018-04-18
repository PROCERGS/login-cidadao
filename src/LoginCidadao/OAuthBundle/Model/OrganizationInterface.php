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
     * @return OrganizationInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return OrganizationInterface
     */
    public function setName($name);

    /**
     * @return PersonInterface[]
     */
    public function getMembers();

    /**
     * @param PersonInterface[] $members
     * @return OrganizationInterface
     */
    public function setMembers(array $members);

    /**
     * @return \DateTime
     */
    public function getVerifiedAt();

    /**
     * @param \DateTime $verifiedAt
     * @return OrganizationInterface
     */
    public function setVerifiedAt($verifiedAt);

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @param string $domain
     * @return OrganizationInterface
     */
    public function setDomain($domain);

    /**
     * @return ClientInterface[]
     */
    public function getClients();

    /**
     * @param ClientInterface[] $clients
     * @return OrganizationInterface
     */
    public function setClients(array $clients);

    /**
     * @return string
     */
    public function getValidationUrl();

    /**
     * @param string $validationUrl
     * @return OrganizationInterface
     */
    public function setValidationUrl($validationUrl);

    /**
     * @return string
     */
    public function getValidationSecret();

    /**
     * @param string $validationSecret
     * @return OrganizationInterface
     */
    public function setValidationSecret($validationSecret);

    /**
     * @return boolean
     */
    public function checkValidation();

    /**
     * @return boolean
     */
    public function isVerified();

    /**
     * Indicates whether or not this Organization is approved/trusted by
     * login-cidadao.
     */
    public function isTrusted();

    /**
     * @param boolean $trusted
     * @return OrganizationInterface
     */
    public function setTrusted($trusted);
}
