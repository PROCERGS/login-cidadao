<?php

namespace LoginCidadao\CoreBundle\Model;

use LoginCidadao\CoreBundle\Entity\City;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\Country;

interface LocationAwareInterface
{

    public function getCity();

    public function setCity(City $city = null);

    public function getState();

    public function setState(State $state = null);

    public function getCountry();

    public function setCountry(Country $country = null);
}
