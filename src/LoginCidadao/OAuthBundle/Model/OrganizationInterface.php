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
     * @return \DateTime
     */
    public function getVerifiedAt();

    /**
     * @param \DateTime $verifiedAt
     */
    public function setVerifiedAt($verifiedAt);

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
     * @param ClientInterface[] $clients
     */
    public function setClients(array $clients);

    /**
     * @return string
     */
    public function getValidationUrl();

    /**
     * @param string $validationUrl
     */
    public function setValidationUrl($validationUrl);

    /**
     * @return string
     */
    public function getValidationSecret();

    /**
     * @param string $validationSecret
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
     */
    public function setTrusted($trusted);
}
