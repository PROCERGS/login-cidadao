<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Model;

use PROCERGS\LoginCidadao\CoreBundle\Entity\Country;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;

class SelectData
{
    /**
     * @var Country
     */
    protected $country;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var City
     */
    protected $city;

    /**
     * @var string
     */
    protected $cityText;

    /**
     * @var string
     */
    protected $stateText;

    /**
     * Set country
     *
     * @param Country $country
     * @return SelectData
     */
    public function setCountry(Country $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set state
     *
     * @param State $state
     * @return SelectData
     */
    public function setState(State $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set city
     *
     * @param City $city
     * @return SelectData
     */
    public function setCity(City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    public function getCityText()
    {
        if ($this->getCity() instanceof City) {
            return $this->getCity()->getName();
        } else {
            return $this->cityText;
        }
    }

    public function getStateText()
    {
        if ($this->getState() instanceof State) {
            return $this->getState()->getName();
        } else {
            return $this->stateText;
        }
    }

    public function setCityText($cityText)
    {
        $this->cityText = $cityText;
        return $this;
    }

    public function setStateText($stateText)
    {
        $this->stateText = $stateText;
        return $this;
    }

    public function getFromObject(LocationAwareInterface $object)
    {
        $this->setCity($object->getCity())
            ->setState($object->getState())
            ->setCountry($object->getCountry());

        return $this;
    }

    public function toObject(LocationAwareInterface $object)
    {
        $object->setCity($this->getCity())
            ->setState($this->getState())
            ->setCountry($this->getCountry());

        return $object;
    }
}