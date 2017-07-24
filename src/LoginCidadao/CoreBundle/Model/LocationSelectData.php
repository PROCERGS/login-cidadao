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

use LoginCidadao\CoreBundle\Entity\Country;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\City;

class LocationSelectData
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
     * @return LocationSelectData
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
     * @return LocationSelectData
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
     * @return LocationSelectData
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
