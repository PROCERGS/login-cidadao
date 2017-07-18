<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\DynamicFormBundle\Model;

use LoginCidadao\CoreBundle\Entity\PersonAddress;
use LoginCidadao\CoreBundle\Model\IdCardInterface;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Model\SelectData;

class DynamicFormData
{
    /** @var PersonInterface */
    protected $person;

    /** @var PersonAddress */
    protected $address;

    /** @var IdCardInterface */
    protected $idCard;

    /** @var string */
    protected $redirectUrl;

    /** @var string */
    protected $scope;

    /** @var string */
    protected $state;

    /**
     * @var SelectData
     */
    protected $placeOfBirth;

    /**
     * @return PersonInterface
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return PersonAddress
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param PersonInterface $person
     * @return DynamicFormData
     */
    public function setPerson(PersonInterface $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @param PersonAddress $address
     * @return DynamicFormData
     */
    public function setAddress(PersonAddress $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return IdCardInterface
     */
    public function getIdCard()
    {
        return $this->idCard;
    }

    /**
     * @param IdCardInterface $idCard
     * @return DynamicFormData
     */
    public function setIdCard(IdCardInterface $idCard)
    {
        $this->idCard = $idCard;

        return $this;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /** @return SelectData */
    public function getPlaceOfBirth()
    {
        return $this->placeOfBirth;
    }

    public function setPlaceOfBirth(SelectData $placeOfBirth)
    {
        $this->placeOfBirth = $placeOfBirth;

        return $this;
    }
}
